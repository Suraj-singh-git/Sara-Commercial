<?php

namespace App\Services\Category;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->categories->paginate($perPage);
    }

    public function create(array $data): Category
    {
        $parentId = $this->extractParentId($data);
        $this->assertValidParent($parentId, null);

        $payload = $this->normalize($data);

        return $this->categories->create($payload);
    }

    public function update(Category $category, array $data): Category
    {
        if (isset($data['max_products'])) {
            $current = $this->categories->countProducts($category->id);

            if ((int) $data['max_products'] < $current) {
                throw ValidationException::withMessages([
                    'max_products' => 'Max products cannot be less than existing products in this category.',
                ]);
            }
        }

        $nextParentId = array_key_exists('parent_id', $data)
            ? $this->parseParentIdValue($data['parent_id'])
            : $category->parent_id;

        $this->assertValidParent($nextParentId, $category);

        return $this->categories->update($category, $this->normalize($data, $category));
    }

    /**
     * @return Collection<int, array{id: int, label: string}>
     */
    public function parentSelectOptions(?Category $except = null): Collection
    {
        $flat = $this->categories->orderedFlat();
        $exclude = $except
            ? array_merge([$except->id], $this->categories->descendantIdsOnly($except->id))
            : [];

        return $flat
            ->filter(fn (Category $c) => ! in_array($c->id, $exclude, true))
            ->values()
            ->map(function (Category $c) {
                $depth = $this->categories->depthOfCategory($c->id);
                $label = str_repeat('— ', $depth).$c->name;

                return ['id' => $c->id, 'label' => $label];
            });
    }

    public function delete(Category $category): void
    {
        if ($this->categories->countChildren($category->id) > 0) {
            throw ValidationException::withMessages([
                'category' => 'Remove or reassign sub-categories before deleting this category.',
            ]);
        }

        if ($this->categories->countProducts($category->id) > 0) {
            throw ValidationException::withMessages([
                'category' => 'Cannot delete a category that still has products.',
            ]);
        }

        $this->categories->delete($category);
    }

    private function assertValidParent(?int $parentId, ?Category $self): void
    {
        if ($parentId === null) {
            return;
        }

        if ($self && $parentId === $self->id) {
            throw ValidationException::withMessages([
                'parent_id' => 'A category cannot be its own parent.',
            ]);
        }

        if ($self && in_array($parentId, $this->categories->descendantIdsOnly($self->id), true)) {
            throw ValidationException::withMessages([
                'parent_id' => 'A category cannot be moved under one of its descendants.',
            ]);
        }

        $parentDepth = $this->categories->depthOfCategory($parentId);
        $tailBelowSelf = $self ? $this->categories->maxDepthBelow($self->id) : 0;

        if ($parentDepth + 1 + $tailBelowSelf > 2) {
            throw ValidationException::withMessages([
                'parent_id' => 'Menu allows at most 3 levels: category → subcategory → sub-subcategory.',
            ]);
        }
    }

    private function extractParentId(array $data): ?int
    {
        if (! array_key_exists('parent_id', $data)) {
            return null;
        }

        return $this->parseParentIdValue($data['parent_id']);
    }

    private function parseParentIdValue(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function normalize(array $data, ?Category $existing = null): array
    {
        $name = $data['name'] ?? $existing?->name;

        $slug = $data['slug'] ?? Str::slug((string) $name);

        return [
            'parent_id' => array_key_exists('parent_id', $data)
                ? $this->parseParentIdValue($data['parent_id'])
                : $existing?->parent_id,
            'name' => $name,
            'slug' => $slug,
            'max_products' => (int) ($data['max_products'] ?? $existing?->max_products ?? 25),
            'sort_order' => (int) ($data['sort_order'] ?? $existing?->sort_order ?? 0),
            'menu_image_path' => $data['menu_image_path'] ?? $existing?->menu_image_path,
            'is_active' => array_key_exists('is_active', $data)
                ? (bool) $data['is_active']
                : (bool) ($existing?->is_active ?? true),
        ];
    }
}
