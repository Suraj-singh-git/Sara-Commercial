<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\DelhiveryShipment;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\OrderStatusEvent;
use App\Models\ProductVariant;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DashboardOrdersSeeder extends Seeder
{
    public function run(): void
    {
        $customers = $this->seedCustomers();
        $variants = ProductVariant::query()
            ->with('product')
            ->where('price', '>', 0)
            ->orderBy('id')
            ->get()
            ->values();

        if ($variants->isEmpty()) {
            $this->command?->warn('DashboardOrdersSeeder skipped: no priced product variants found.');

            return;
        }

        $statuses = [
            OrderStatus::Delivered,
            OrderStatus::InTransit,
            OrderStatus::Dispatched,
            OrderStatus::Processing,
            OrderStatus::Pending,
            OrderStatus::Cancelled,
        ];

        $modes = [
            PaymentMode::FullOnline,
            PaymentMode::PartialCod,
            PaymentMode::Cod,
        ];

        $seeded = 0;
        $seq = 0;

        // Seed orders across the last 3 calendar months (including current month).
        for ($monthOffset = 2; $monthOffset >= 0; $monthOffset--) {
            $monthStart = CarbonImmutable::now()->subMonths($monthOffset)->startOfMonth();

            for ($slot = 1; $slot <= 8; $slot++) {
                $seq++;
                $status = $statuses[($slot - 1) % count($statuses)];
                $mode = $modes[($seq - 1) % count($modes)];
                $user = $customers[($seq - 1) % count($customers)];
                $placedAt = $monthStart->addDays(min(26, 2 + ($slot * 3)))->setTime(10 + ($slot % 7), 15);

                $reference = sprintf('SR-DB-%s-%02d', $monthStart->format('Ym'), $slot);
                $subtotal = 0.0;
                $tax = 0.0;
                $shipping = 0.0;

                $order = Order::query()->updateOrCreate(
                    ['reference' => $reference],
                    [
                        'user_id' => $user->id,
                        'status' => $status->value,
                        'payment_status' => $this->derivePaymentStatus($status, $mode)->value,
                        'payment_mode' => $mode->value,
                        'subtotal' => 0,
                        'shipping_total' => 0,
                        'tax_total' => 0,
                        'grand_total' => 0,
                        'delay_reason' => null,
                        'delay_days' => null,
                        'placed_at' => $placedAt,
                    ]
                );

                $order->items()->delete();
                $order->addresses()->delete();
                $order->statusEvents()->delete();
                DelhiveryShipment::query()->where('order_id', $order->id)->delete();

                $itemCount = 1 + ($seq % 3);
                for ($i = 0; $i < $itemCount; $i++) {
                    $variant = $variants->get(($seq + $i) % $variants->count());
                    if (! $variant instanceof ProductVariant) {
                        continue;
                    }

                    $quantity = 1 + (($slot + $i) % 3);
                    $unit = (float) $variant->price;
                    $line = $unit * $quantity;
                    $subtotal += $line;

                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_variant_id' => $variant->id,
                        'product_name' => $variant->product->name,
                        'variant_label' => $variant->label(),
                        'unit_price' => $unit,
                        'quantity' => $quantity,
                        'line_total' => $line,
                    ]);
                }

                $grand = $subtotal + $shipping + $tax;

                $delayReason = null;
                $delayDays = null;
                if ($status === OrderStatus::Pending && $slot % 5 === 0) {
                    $status = OrderStatus::Delayed;
                    $delayReason = 'Carrier routing delay due to weather.';
                    $delayDays = 2;
                }

                $waybill = null;
                if (in_array($status, [OrderStatus::Dispatched, OrderStatus::InTransit, OrderStatus::Delivered], true)) {
                    $waybill = 'WB'.strtoupper(Str::random(10));
                    DelhiveryShipment::query()->create([
                        'order_id' => $order->id,
                        'waybill' => $waybill,
                        'status' => $status === OrderStatus::Delivered ? 'delivered' : ($status === OrderStatus::InTransit ? 'in transit' : 'shipped'),
                        'last_payload' => ['source' => 'dashboard_seeder'],
                    ]);
                }

                $order->update([
                    'status' => $status->value,
                    'payment_status' => $this->derivePaymentStatus($status, $mode)->value,
                    'payment_mode' => $mode->value,
                    'subtotal' => $subtotal,
                    'shipping_total' => $shipping,
                    'tax_total' => $tax,
                    'grand_total' => $grand,
                    'delay_reason' => $delayReason,
                    'delay_days' => $delayDays,
                    'delhivery_waybill' => $waybill,
                    'placed_at' => $placedAt,
                ]);

                OrderAddress::query()->create([
                    'order_id' => $order->id,
                    'type' => 'shipping',
                    'contact_name' => $user->name,
                    'phone' => $user->phone ?? '9990000000',
                    'line1' => 'Industrial Area, Sector '.(10 + ($slot % 8)),
                    'line2' => 'Warehouse Block '.chr(65 + ($slot % 5)),
                    'city' => ['Delhi', 'Noida', 'Lucknow', 'Pune', 'Jaipur'][($seq - 1) % 5],
                    'state' => ['DL', 'UP', 'MH', 'RJ', 'KA'][($seq - 1) % 5],
                    'postal_code' => (string) (110001 + ($seq * 3)),
                    'country' => 'IN',
                ]);

                foreach ($this->statusTimeline($status, $placedAt) as $index => $state) {
                    OrderStatusEvent::query()->create([
                        'order_id' => $order->id,
                        'status' => $state->value,
                        'meta' => ['source' => 'dashboard_seeder', 'step' => $index + 1],
                        'recorded_at' => $placedAt->addHours($index * 8),
                    ]);
                }

                $seeded++;
            }
        }

        $this->command?->info("DashboardOrdersSeeder seeded {$seeded} orders across last 3 months.");
    }

    /**
     * @return array<int, User>
     */
    private function seedCustomers(): array
    {
        $rows = [
            ['name' => 'Sample Buyer', 'email' => 'buyer@sara.local', 'phone' => '9990001111'],
            ['name' => 'Anita Sharma', 'email' => 'anita@sara.local', 'phone' => '9990002222'],
            ['name' => 'Ravi Kumar', 'email' => 'ravi@sara.local', 'phone' => '9990003333'],
            ['name' => 'Meera Singh', 'email' => 'meera@sara.local', 'phone' => '9990004444'],
        ];

        $users = [];
        foreach ($rows as $row) {
            $users[] = User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'phone' => $row['phone'],
                    'password' => Hash::make('password'),
                    'role' => UserRole::Customer,
                    'email_verified_at' => now(),
                ]
            );
        }

        return $users;
    }

    private function derivePaymentStatus(OrderStatus $status, PaymentMode $mode): PaymentStatus
    {
        return match ($mode) {
            PaymentMode::FullOnline => in_array($status, [OrderStatus::Processing, OrderStatus::Dispatched, OrderStatus::InTransit, OrderStatus::Delivered], true)
                ? PaymentStatus::Paid
                : PaymentStatus::Pending,
            PaymentMode::PartialCod => in_array($status, [OrderStatus::Dispatched, OrderStatus::InTransit, OrderStatus::Delivered], true)
                ? PaymentStatus::Partial
                : PaymentStatus::Pending,
            PaymentMode::Cod => PaymentStatus::Pending,
        };
    }

    /**
     * @return list<OrderStatus>
     */
    private function statusTimeline(OrderStatus $status, CarbonImmutable $placedAt): array
    {
        return match ($status) {
            OrderStatus::Pending => [OrderStatus::Pending],
            OrderStatus::Processing => [OrderStatus::Pending, OrderStatus::Processing],
            OrderStatus::Dispatched => [OrderStatus::Pending, OrderStatus::Processing, OrderStatus::Dispatched],
            OrderStatus::InTransit => [OrderStatus::Pending, OrderStatus::Processing, OrderStatus::Dispatched, OrderStatus::InTransit],
            OrderStatus::Delivered => [OrderStatus::Pending, OrderStatus::Processing, OrderStatus::Dispatched, OrderStatus::InTransit, OrderStatus::Delivered],
            OrderStatus::Cancelled => [OrderStatus::Pending, OrderStatus::Cancelled],
            OrderStatus::Delayed => [OrderStatus::Pending, OrderStatus::Processing, OrderStatus::Delayed],
        };
    }
}

