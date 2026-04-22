<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface
{
    public function paginateForAdmin(array $filters, int $perPage = 20): LengthAwarePaginator;

    public function find(int $id): ?Order;

    public function findByReference(string $reference): ?Order;

    public function forUser(int $userId): Collection;

    public function create(array $attributes): Order;

    public function updateOrder(Order $order, array $attributes): Order;

    public function aggregateRevenue(?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): float;

    public function aggregateGrossValue(?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): float;

    public function countOrders(?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): int;

    public function countActiveUsers(?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): int;
}
