<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function paginateForAdmin(int $perPage = 15): LengthAwarePaginator;

    public function paginateVisible(array $filters, int $perPage = 12): LengthAwarePaginator;

    public function find(int $id): ?Product;

    public function findBySlug(string $slug): ?Product;

    public function featured(int $limit = 8): \Illuminate\Database\Eloquent\Collection;

    public function create(array $data): Product;

    public function update(Product $product, array $data): Product;

    public function delete(Product $product): void;
}
