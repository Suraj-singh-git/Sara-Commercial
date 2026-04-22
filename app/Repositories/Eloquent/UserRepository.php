<?php

namespace App\Repositories\Eloquent;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function paginateCustomers(int $perPage = 20): LengthAwarePaginator
    {
        return User::query()
            ->where('role', UserRole::Customer)
            ->withCount('orders')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function find(int $id): ?User
    {
        return User::query()->with(['orders' => fn ($q) => $q->orderByDesc('id')])->find($id);
    }

    public function exportRows(): Collection
    {
        return User::query()
            ->where('role', UserRole::Customer)
            ->withCount('orders')
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'email', 'orders_count']);
    }
}
