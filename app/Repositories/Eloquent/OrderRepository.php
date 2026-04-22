<?php

namespace App\Repositories\Eloquent;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository implements OrderRepositoryInterface
{
    public function paginateForAdmin(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Order::query()
            ->with(['user', 'items.variant.product.images'])
            ->orderByDesc('id');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('placed_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('placed_at', '<=', $filters['to']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function find(int $id): ?Order
    {
        return Order::query()->with(['user', 'items.variant.product.images', 'addresses', 'shippingAddress', 'statusEvents', 'shipment'])->find($id);
    }

    public function findByReference(string $reference): ?Order
    {
        return Order::query()->with(['user', 'items.variant.product.images', 'addresses', 'statusEvents'])->where('reference', $reference)->first();
    }

    public function forUser(int $userId): Collection
    {
        return Order::query()
            ->with(['items.variant.product.images'])
            ->where('user_id', $userId)
            ->orderByDesc('placed_at')
            ->get();
    }

    public function create(array $attributes): Order
    {
        return Order::query()->create($attributes);
    }

    public function updateOrder(Order $order, array $attributes): Order
    {
        $order->update($attributes);

        return $order->fresh(['user', 'items', 'addresses', 'statusEvents']);
    }

    public function aggregateRevenue(?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): float
    {
        $query = Order::query()->where('payment_status', PaymentStatus::Paid->value);

        $this->applyPlacedBetween($query, $from, $to);

        return (float) $query->sum('grand_total');
    }

    public function aggregateGrossValue(?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): float
    {
        $query = Order::query()->where('status', '!=', 'cancelled');

        $this->applyPlacedBetween($query, $from, $to);

        return (float) $query->sum('grand_total');
    }

    public function countOrders(?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): int
    {
        $query = Order::query();
        $this->applyPlacedBetween($query, $from, $to);

        return $query->count();
    }

    public function countActiveUsers(?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): int
    {
        $query = Order::query()->select('user_id')->distinct();

        $this->applyPlacedBetween($query, $from, $to);

        return $query->count('user_id');
    }

    private function applyPlacedBetween(Builder $query, ?\DateTimeInterface $from, ?\DateTimeInterface $to): void
    {
        if ($from) {
            $query->whereDate('placed_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('placed_at', '<=', $to);
        }
    }
}
