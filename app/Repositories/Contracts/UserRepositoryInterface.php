<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function paginateCustomers(int $perPage = 20): LengthAwarePaginator;

    public function find(int $id): ?User;

    public function exportRows(): Collection;
}
