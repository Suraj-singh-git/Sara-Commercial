<?php

namespace App\Services\Checkout;

use App\Enums\OrderStatus;
use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\OrderStatusEvent;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\Notification\NotificationService;
use App\Services\Payment\RazorpayService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly CartRepositoryInterface $cart,
        private readonly OrderRepositoryInterface $orders,
        private readonly RazorpayService $razorpay,
        private readonly NotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $address
     */
    public function placeOrder(?string $sessionId, string $paymentMode, array $address): Order
    {
        $userId = Auth::id();

        if (! $userId) {
            throw ValidationException::withMessages(['auth' => 'Please login to checkout.']);
        }

        $items = $this->cart->items($userId, $sessionId);

        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        $mode = PaymentMode::tryFrom($paymentMode);

        if (! $mode) {
            throw ValidationException::withMessages(['payment_mode' => 'Invalid payment mode.']);
        }

        return DB::transaction(function () use ($items, $userId, $mode, $address, $sessionId) {
            $subtotal = 0.0;

            foreach ($items as $row) {
                $price = (float) $row->variant->price;
                $subtotal += $price * $row->quantity;
            }

            $shipping = 0.0;
            $tax = 0.0;
            $grand = $subtotal + $shipping + $tax;

            $paymentStatus = match ($mode) {
                PaymentMode::Cod => PaymentStatus::Pending,
                PaymentMode::PartialCod => PaymentStatus::Partial,
                PaymentMode::FullOnline => PaymentStatus::Pending,
            };

            $order = $this->orders->create([
                'reference' => 'SR-'.strtoupper(Str::random(8)),
                'user_id' => $userId,
                'status' => OrderStatus::Pending->value,
                'payment_status' => $paymentStatus->value,
                'payment_mode' => $mode->value,
                'subtotal' => $subtotal,
                'shipping_total' => $shipping,
                'tax_total' => $tax,
                'grand_total' => $grand,
                'placed_at' => now(),
            ]);

            foreach ($items as $row) {
                $variant = $row->variant;
                $unit = (float) $variant->price;
                $qty = (int) $row->quantity;

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_label' => $variant->label(),
                    'unit_price' => $unit,
                    'quantity' => $qty,
                    'line_total' => $unit * $qty,
                ]);
            }

            OrderAddress::query()->create([
                'order_id' => $order->id,
                'type' => 'shipping',
                'contact_name' => $address['contact_name'],
                'phone' => $address['phone'],
                'line1' => $address['line1'],
                'line2' => $address['line2'] ?? null,
                'city' => $address['city'],
                'state' => $address['state'] ?? null,
                'postal_code' => $address['postal_code'],
                'country' => $address['country'] ?? 'IN',
            ]);

            OrderStatusEvent::query()->create([
                'order_id' => $order->id,
                'status' => OrderStatus::Pending->value,
                'meta' => ['source' => 'checkout'],
                'recorded_at' => now(),
            ]);

            $intent = $this->razorpay->createOrderIntent($order);

            $this->orders->updateOrder($order, [
                'razorpay_order_id' => $intent['id'] ?? null,
            ]);

            $this->cart->clear($userId, $sessionId);

            $fresh = $this->orders->find($order->id);

            $this->notifications->orderPlaced($fresh);
            $this->notifications->adminNewOrder($fresh);

            return $fresh;
        });
    }
}
