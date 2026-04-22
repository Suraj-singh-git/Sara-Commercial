<?php

namespace App\Services\Shipping;

use App\Enums\OrderStatus;
use App\Models\DelhiveryShipment;
use App\Models\Order;
use App\Models\OrderStatusEvent;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DelhiveryService
{
    public function __construct(
        private readonly NotificationService $notifications,
    ) {}

    public function ensureShipment(Order $order): DelhiveryShipment
    {
        $shipment = DelhiveryShipment::query()->firstOrCreate(
            ['order_id' => $order->id],
            [
                'waybill' => 'WB'.strtoupper(Str::random(10)),
                'status' => 'booked',
                'last_payload' => ['source' => 'stub'],
            ],
        );

        if ($shipment->wasRecentlyCreated || ! $order->delhivery_waybill) {
            $order->forceFill(['delhivery_waybill' => $shipment->waybill])->save();
        }

        return $shipment;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function applyTrackingUpdate(string $waybill, array $payload): ?Order
    {
        $shipment = DelhiveryShipment::query()->where('waybill', $waybill)->first();

        if (! $shipment) {
            return null;
        }

        $rawStatus = (string) ($payload['status'] ?? $payload['Status'] ?? 'unknown');

        $shipment->update([
            'status' => $rawStatus,
            'last_payload' => $payload,
        ]);

        Log::info('Delhivery stub webhook', ['waybill' => $waybill, 'payload' => $payload]);

        $order = $shipment->order;
        if (! $order) {
            return null;
        }

        $mappedStatus = $this->mapCarrierStatus($rawStatus);
        if (! $mappedStatus || $order->status === $mappedStatus->value) {
            return $order;
        }

        $order->forceFill(['status' => $mappedStatus->value])->save();

        OrderStatusEvent::query()->create([
            'order_id' => $order->id,
            'status' => $mappedStatus->value,
            'meta' => [
                'source' => 'delhivery_webhook',
                'carrier_status' => $rawStatus,
            ],
            'recorded_at' => now(),
        ]);

        if ($mappedStatus === OrderStatus::Dispatched) {
            $this->notifications->orderDispatched($order->fresh('user'));
        } elseif ($mappedStatus === OrderStatus::Delivered) {
            $this->notifications->orderDelivered($order->fresh('user'));
        }

        return $order->fresh();
    }

    private function mapCarrierStatus(string $status): ?OrderStatus
    {
        $normalized = strtolower(trim($status));

        return match (true) {
            str_contains($normalized, 'picked'),
            str_contains($normalized, 'manifested'),
            str_contains($normalized, 'dispatched'),
            str_contains($normalized, 'shipped') => OrderStatus::Dispatched,

            str_contains($normalized, 'in transit'),
            str_contains($normalized, 'in_transit'),
            str_contains($normalized, 'out for delivery'),
            str_contains($normalized, 'ofd') => OrderStatus::InTransit,

            str_contains($normalized, 'delivered') => OrderStatus::Delivered,
            str_contains($normalized, 'cancel') => OrderStatus::Cancelled,
            default => null,
        };
    }
}
