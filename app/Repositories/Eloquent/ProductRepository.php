<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    public function paginateForAdmin(int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with(['category', 'variants', 'images'])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function paginateVisible(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $query = Product::query()
            ->where('is_visible', true)
            ->with(['category', 'variants', 'images']);

        if (! empty($filters['category_id'])) {
            $ids = $this->categories->descendantIdsIncluding((int) $filters['category_id']);
            $query->whereIn('category_id', $ids);
        }

        $minPrice = isset($filters['min_price']) && $filters['min_price'] !== '' ? (float) $filters['min_price'] : null;
        $maxPrice = isset($filters['max_price']) && $filters['max_price'] !== '' ? (float) $filters['max_price'] : null;

        if ($minPrice !== null || $maxPrice !== null) {
            $query->whereHas('variants', function (Builder $q) use ($minPrice, $maxPrice) {
                // Ignore addon/free variants (e.g., warranty rows at 0) for catalog price filters.
                $q->where('price', '>', 0);

                if ($minPrice !== null) {
                    $q->where('price', '>=', $minPrice);
                }

                if ($maxPrice !== null) {
                    $q->where('price', '<=', $maxPrice);
                }
            });
        }

        if (! empty($filters['q'])) {
            $term = '%'.addcslashes(trim((string) $filters['q']), '%_\\').'%';
            $query->where('name', 'like', $term);
        }

        return $query->orderBy('name')->paginate($perPage)->withQueryString();
    }

    public function find(int $id): ?Product
    {
        return Product::query()->with(['category', 'variants', 'images'])->find($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        return Product::query()->with(['category', 'variants', 'images'])->where('slug', $slug)->first();
    }

    public function featured(int $limit = 8): \Illuminate\Database\Eloquent\Collection
    {
        return Product::query()
            ->where('is_visible', true)
            ->with(['category', 'variants', 'images'])
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh(['category', 'variants', 'images']);
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
