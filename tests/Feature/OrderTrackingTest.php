<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderStatusEvent;
use App\Models\User;
use App\Services\Order\OrderTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracking_marks_delivered_last_step_done(): void
    {
        $user = User::factory()->create(['role' => UserRole::Customer]);

        $order = Order::query()->create([
            'reference' => 'SR-TEST123',
            'user_id' => $user->id,
            'status' => OrderStatus::Delivered->value,
            'payment_status' => PaymentStatus::Paid->value,
            'payment_mode' => PaymentMode::Cod->value,
            'subtotal' => 100,
            'shipping_total' => 0,
            'tax_total' => 0,
            'grand_total' => 100,
            'placed_at' => now()->subDay(),
        ]);

        OrderStatusEvent::query()->create([
            'order_id' => $order->id,
            'status' => OrderStatus::Pending->value,
            'meta' => null,
            'recorded_at' => now()->subDay(),
        ]);

        $order->load('statusEvents', 'shipment');

        $tracking = app(OrderTrackingService::class)->build($order);

        $this->assertCount(5, $tracking['steps']);
        $this->assertSame(4, $tracking['current_index']);
        $this->assertSame('current', $tracking['steps'][4]['state']);
        $this->assertSame('done', $tracking['steps'][0]['state']);
    }
}
