<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function allActive(): Collection;

    public function find(int $id): ?Category;

    public function findBySlug(string $slug): ?Category;

    public function create(array $data): Category;

    public function update(Category $category, array $data): Category;

    public function delete(Category $category): void;

    public function countProducts(int $categoryId): int;

    /**
     * Root categories with two levels of children for the storefront drawer.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Category>
     */
    public function menuTree(): \Illuminate\Database\Eloquent\Collection;

    /**
     * @return list<int>
     */
    public function descendantIdsIncluding(int $categoryId): array;

    /**
     * @return list<int>
     */
    public function descendantIdsOnly(int $categoryId): array;

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Category>
     */
    public function orderedFlat(): \Illuminate\Database\Eloquent\Collection;

    public function countChildren(int $categoryId): int;

    public function depthOfCategory(int $categoryId): int;

    /**
     * Additional levels below this category (0 = leaf).
     */
    public function maxDepthBelow(int $categoryId): int;
}
