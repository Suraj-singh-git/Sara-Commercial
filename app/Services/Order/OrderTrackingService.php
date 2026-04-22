<?php

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class OrderTrackingService
{
    /**
     * Build marketplace-style tracking (ordered → shipped → in transit → delivered).
     *
     * @return array{
     *     steps: list<array{key: string, title: string, detail: string, state: string, at: ?Carbon}>,
     *     current_index: int,
     *     is_cancelled: bool,
     *     delay: ?array{reason: string, days: ?int},
     *     waybill: ?string,
     *     carrier_status: ?string,
     *     activity: Collection<int, array{time: ?Carbon, title: string, detail: string}>
     * }
     */
    public function build(Order $order): array
    {
        $cancelled = $order->status === OrderStatus::Cancelled;
        $delayed = $order->status === OrderStatus::Delayed;

        $milestoneStatuses = [
            'ordered' => OrderStatus::Pending,
            'processing' => OrderStatus::Processing,
            'shipped' => OrderStatus::Dispatched,
            'transit' => OrderStatus::InTransit,
            'delivered' => OrderStatus::Delivered,
        ];

        $titles = [
            'ordered' => ['Order placed', 'We have received your order.'],
            'processing' => ['Processing', 'Seller is preparing your items for shipment.'],
            'shipped' => ['Shipped', 'Package handed to the delivery partner.'],
            'transit' => ['On the way', 'Your package is moving toward your address.'],
            'delivered' => ['Delivered', 'Package has been delivered.'],
        ];

        $currentIndex = $this->resolveCurrentStepIndex($order, $cancelled, $delayed);

        $steps = [];
        $keys = array_keys($titles);
        foreach ($keys as $index => $key) {
            $target = $milestoneStatuses[$key];
            $at = $this->firstEventAt($order, $target);

            if ($key === 'ordered' && $at === null) {
                $at = $order->placed_at;
            }

            [$title, $detail] = $titles[$key];

            if ($cancelled) {
                $steps[] = [
                    'key' => $key,
                    'title' => $title,
                    'detail' => $detail,
                    'state' => 'void',
                    'at' => $at,
                ];

                continue;
            }

            $state = 'upcoming';
            if ($index < $currentIndex) {
                $state = 'done';
            } elseif ($index === $currentIndex) {
                $state = 'current';
            }

            if ($delayed && $index === $currentIndex && $key !== 'delivered') {
                $detail = 'Your shipment is delayed. '.$this->delaySummary($order);
            }

            $steps[] = [
                'key' => $key,
                'title' => $title,
                'detail' => $detail,
                'state' => $state,
                'at' => $at,
            ];
        }

        $shipment = $order->shipment;
        $carrierStatus = $shipment?->status;
        $waybill = $order->delhivery_waybill ?? $shipment?->waybill;

        return [
            'steps' => $steps,
            'current_index' => $cancelled ? -1 : $currentIndex,
            'is_cancelled' => $cancelled,
            'delay' => $delayed || $order->delay_reason
                ? [
                    'reason' => (string) ($order->delay_reason ?? ''),
                    'days' => $order->delay_days,
                    'active' => $delayed,
                ]
                : null,
            'waybill' => $waybill,
            'carrier_status' => $carrierStatus,
            'activity' => $this->buildActivity($order),
        ];
    }

    private function resolveCurrentStepIndex(Order $order, bool $cancelled, bool $delayed): int
    {
        if ($cancelled) {
            return -1;
        }

        if ($delayed) {
            $prev = $this->inferIndexBeforeDelay($order);

            return min(3, max(0, $prev));
        }

        return match ($order->status) {
            OrderStatus::Pending => 0,
            OrderStatus::Processing => 1,
            OrderStatus::Dispatched => 2,
            OrderStatus::InTransit => 3,
            OrderStatus::Delivered => 4,
            default => 0,
        };
    }

    private function inferIndexBeforeDelay(Order $order): int
    {
        $events = $order->statusEvents->sortBy('recorded_at');
        $lastNonDelay = $events->filter(fn (OrderStatusEvent $e) => $e->status !== OrderStatus::Delayed)->last();

        return match ($lastNonDelay?->status) {
            OrderStatus::Delivered => 4,
            OrderStatus::InTransit => 3,
            OrderStatus::Dispatched => 2,
            OrderStatus::Processing => 1,
            OrderStatus::Pending => 0,
            default => 2,
        };
    }

    private function firstEventAt(Order $order, OrderStatus $status): ?Carbon
    {
        $event = $order->statusEvents->first(fn (OrderStatusEvent $e) => $e->status === $status);

        return $event?->recorded_at;
    }

    private function delaySummary(Order $order): string
    {
        $parts = array_filter([
            $order->delay_reason,
            $order->delay_days ? 'Expected delay: '.$order->delay_days.' day(s).' : null,
        ]);

        return $parts === [] ? 'Our team is working on it.' : implode(' ', $parts);
    }

    /**
     * @return Collection<int, array{time: ?Carbon, title: string, detail: string}>
     */
    private function buildActivity(Order $order): Collection
    {
        return $order->statusEvents
            ->sortByDesc('recorded_at')
            ->values()
            ->map(function (OrderStatusEvent $event) {
                $label = match ($event->status) {
                    OrderStatus::Pending => 'Order placed',
                    OrderStatus::Processing => 'Processing started',
                    OrderStatus::Dispatched => 'Shipped from facility',
                    OrderStatus::InTransit => 'In transit',
                    OrderStatus::Delivered => 'Delivered',
                    OrderStatus::Cancelled => 'Order cancelled',
                    OrderStatus::Delayed => 'Delivery delayed',
                };

                $detail = '';
                if ($event->status === OrderStatus::Delayed && is_array($event->meta)) {
                    $detail = (string) ($event->meta['reason'] ?? '');
                }

                return [
                    'time' => $event->recorded_at,
                    'title' => $label,
                    'detail' => $detail,
                ];
            });
    }
}
