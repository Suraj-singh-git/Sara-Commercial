<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly NotificationService $notifications,
    ) {}

    /**
     * @return array{id: string}
     */
    public function createOrderIntent(Order $order): array
    {
        $id = 'rzp_stub_'.uniqid();

        Log::info('Razorpay stub order created', [
            'reference' => $order->reference,
            'amount' => $order->grand_total,
            'order_id' => $id,
        ]);

        return ['id' => $id];
    }

    public function markPaidFromWebhook(string $reference, ?string $paymentId = null): ?Order
    {
        $order = Order::query()->where('reference', $reference)->first();

        if (! $order) {
            return null;
        }

        $updated = $this->orders->updateOrder($order, [
            'payment_status' => PaymentStatus::Paid->value,
            'razorpay_payment_id' => $paymentId ?? ('pay_stub_'.uniqid()),
        ]);

        Log::info('Razorpay stub payment captured', ['reference' => $reference]);

        $this->notifications->paymentSuccess($updated);

        return $updated;
    }
}
