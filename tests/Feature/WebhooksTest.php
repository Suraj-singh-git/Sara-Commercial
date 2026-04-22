<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Models\DelhiveryShipment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhooksTest extends TestCase
{
    use RefreshDatabase;

    public function test_razorpay_webhook_marks_order_paid_when_signature_is_valid(): void
    {
        config()->set('services.razorpay.webhook_secret', 'test_secret');

        $user = User::factory()->create();
        $order = Order::query()->create([
            'reference' => 'SR-TESTPAY1',
            'user_id' => $user->id,
            'status' => OrderStatus::Pending->value,
            'payment_status' => PaymentStatus::Pending->value,
            'payment_mode' => PaymentMode::FullOnline->value,
            'subtotal' => 1000,
            'shipping_total' => 0,
            'tax_total' => 0,
            'grand_total' => 1000,
            'placed_at' => now(),
        ]);

        $payload = ['reference' => $order->reference, 'payment_id' => 'pay_test_1'];
        $raw = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $raw, 'test_secret');

        $response = $this->withHeader('X-Razorpay-Signature', $signature)
            ->postJson(route('webhooks.razorpay'), $payload);

        $response->assertOk();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => PaymentStatus::Paid->value,
            'razorpay_payment_id' => 'pay_test_1',
        ]);
    }

    public function test_delhivery_webhook_updates_order_status_and_logs_event(): void
    {
        config()->set('services.delhivery.webhook_token', 'token123');

        $user = User::factory()->create();
        $order = Order::query()->create([
            'reference' => 'SR-TESTSHIP1',
            'user_id' => $user->id,
            'status' => OrderStatus::Processing->value,
            'payment_status' => PaymentStatus::Paid->value,
            'payment_mode' => PaymentMode::FullOnline->value,
            'subtotal' => 2000,
            'shipping_total' => 0,
            'tax_total' => 0,
            'grand_total' => 2000,
            'placed_at' => now(),
        ]);

        DelhiveryShipment::query()->create([
            'order_id' => $order->id,
            'waybill' => 'WBTEST123',
            'status' => 'booked',
        ]);

        $response = $this->withHeader('X-Delhivery-Token', 'token123')
            ->postJson(route('webhooks.delhivery'), [
                'waybill' => 'WBTEST123',
                'status' => 'Out for delivery',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::InTransit->value,
        ]);

        $this->assertDatabaseHas('order_status_events', [
            'order_id' => $order->id,
            'status' => OrderStatus::InTransit->value,
        ]);
    }
}

