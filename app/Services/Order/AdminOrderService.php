<?php

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusEvent;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\Notification\NotificationService;
use App\Services\Shipping\DelhiveryService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class AdminOrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly NotificationService $notifications,
        private readonly DelhiveryService $delhivery,
    ) {}

    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->orders->paginateForAdmin($filters, $perPage);
    }

    public function find(int $id): ?Order
    {
        return $this->orders->find($id);
    }

    public function updateStatus(Order $order, string $status): Order
    {
        $new = OrderStatus::tryFrom($status);

        if (! $new) {
            throw ValidationException::withMessages(['status' => 'Invalid status.']);
        }

        $updated = $this->orders->updateOrder($order, ['status' => $new->value]);

        OrderStatusEvent::query()->create([
            'order_id' => $updated->id,
            'status' => $new->value,
            'meta' => ['source' => 'admin'],
            'recorded_at' => now(),
        ]);

        if ($new === OrderStatus::Processing) {
            $this->delhivery->ensureShipment($updated);
        }

        if ($new === OrderStatus::Dispatched) {
            $this->notifications->orderDispatched($updated);
        }

        if ($new === OrderStatus::Delivered) {
            $this->notifications->orderDelivered($updated);
        }

        return $updated;
    }

    public function markDelayed(Order $order, string $reason, int $days): Order
    {
        $updated = $this->orders->updateOrder($order, [
            'status' => OrderStatus::Delayed->value,
            'delay_reason' => $reason,
            'delay_days' => $days,
        ]);

        OrderStatusEvent::query()->create([
            'order_id' => $updated->id,
            'status' => OrderStatus::Delayed->value,
            'meta' => ['reason' => $reason, 'days' => $days],
            'recorded_at' => now(),
        ]);

        return $updated;
    }
}
