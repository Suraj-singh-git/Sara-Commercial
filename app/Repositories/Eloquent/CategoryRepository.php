<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Category::query()
            ->with('parent')
            ->orderByRaw('parent_id is null desc')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function allActive(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function menuTree(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with([
                'children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('name')
                    ->with(['children' => fn ($q2) => $q2->where('is_active', true)->orderBy('sort_order')->orderBy('name')]),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function descendantIdsIncluding(int $categoryId): array
    {
        return array_merge([$categoryId], $this->descendantIdsOnly($categoryId));
    }

    public function descendantIdsOnly(int $categoryId): array
    {
        $ids = [];
        $children = Category::query()->where('parent_id', $categoryId)->pluck('id');
        foreach ($children as $cid) {
            $ids[] = (int) $cid;
            $ids = array_merge($ids, $this->descendantIdsOnly((int) $cid));
        }

        return $ids;
    }

    public function orderedFlat(): Collection
    {
        return Category::query()
            ->orderByRaw('parent_id is null desc')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function countChildren(int $categoryId): int
    {
        return Category::query()->where('parent_id', $categoryId)->count();
    }

    public function find(int $id): ?Category
    {
        return Category::query()->find($id);
    }

    public function findBySlug(string $slug): ?Category
    {
        return Category::query()->where('slug', $slug)->first();
    }

    public function create(array $data): Category
    {
        return Category::query()->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->fresh();
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    public function countProducts(int $categoryId): int
    {
        return Product::query()->where('category_id', $categoryId)->count();
    }

    public function depthOfCategory(int $categoryId): int
    {
        $depth = 0;
        $parentId = Category::query()->whereKey($categoryId)->value('parent_id');
        while ($parentId) {
            $depth++;
            $parentId = Category::query()->whereKey($parentId)->value('parent_id');
            if ($depth > 20) {
                break;
            }
        }

        return $depth;
    }

    public function maxDepthBelow(int $categoryId): int
    {
        $max = 0;
        $children = Category::query()->where('parent_id', $categoryId)->pluck('id');
        foreach ($children as $cid) {
            $max = max($max, 1 + $this->maxDepthBelow((int) $cid));
        }

        return $max;
    }
}
