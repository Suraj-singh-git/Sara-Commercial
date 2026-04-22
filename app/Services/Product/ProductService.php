<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    public function create(array $product, array $variants, array $uploadedImages = []): Product
    {
        $category = $this->categories->find((int) $product['category_id']);

        if (! $category) {
            throw ValidationException::withMessages(['category_id' => 'Invalid category.']);
        }

        $this->assertVariantCount($variants);
        $this->assertCategoryHasRoom($category->id, (int) $category->max_products);

        return DB::transaction(function () use ($product, $variants, $uploadedImages, $category) {
            $model = $this->products->create([
                'category_id' => $category->id,
                'name' => $product['name'],
                'slug' => $product['slug'] ?? Str::slug($product['name']),
                'short_description' => $product['short_description'],
                'detailed_description' => $product['detailed_description'],
                'is_visible' => (bool) ($product['is_visible'] ?? true),
            ]);

            $this->syncVariants($model, $variants);
            $this->storeUploadedImages($model, $uploadedImages);

            return $model->fresh(['category', 'variants', 'images']);
        });
    }

    public function update(Product $product, array $productData, array $variants, array $uploadedImages = []): Product
    {
        if (isset($productData['category_id'])) {
            $category = $this->categories->find((int) $productData['category_id']);

            if (! $category) {
                throw ValidationException::withMessages(['category_id' => 'Invalid category.']);
            }

            $targetCategoryId = $category->id;
        } else {
            $targetCategoryId = $product->category_id;
            $category = $this->categories->find($targetCategoryId);
        }

        $this->assertVariantCount($variants);

        if ($targetCategoryId !== $product->category_id) {
            $this->assertCategoryHasRoom($targetCategoryId, (int) $category->max_products);
        }

        return DB::transaction(function () use ($product, $productData, $variants, $uploadedImages) {
            $this->products->update($product, [
                'category_id' => (int) ($productData['category_id'] ?? $product->category_id),
                'name' => $productData['name'] ?? $product->name,
                'slug' => $productData['slug'] ?? $product->slug,
                'short_description' => $productData['short_description'] ?? $product->short_description,
                'detailed_description' => $productData['detailed_description'] ?? $product->detailed_description,
                'is_visible' => array_key_exists('is_visible', $productData)
                    ? (bool) $productData['is_visible']
                    : $product->is_visible,
            ]);

            $product->variants()->delete();
            $this->syncVariants($product->fresh(), $variants);

            if ($uploadedImages !== []) {
                $this->storeUploadedImages($product->fresh(), $uploadedImages);
            }

            return $product->fresh(['category', 'variants', 'images']);
        });
    }

    public function delete(Product $product): void
    {
        $this->products->delete($product);
    }

    public function toggleVisibility(Product $product, bool $visible): Product
    {
        return $this->products->update($product, ['is_visible' => $visible]);
    }

    private function assertVariantCount(array $variants): void
    {
        $count = count($variants);

        if ($count < 1 || $count > 6) {
            throw ValidationException::withMessages([
                'variants' => 'Each product must have between 1 and 6 variants.',
            ]);
        }
    }

    private function assertCategoryHasRoom(int $categoryId, int $maxProducts): void
    {
        $count = $this->categories->countProducts($categoryId);

        if ($count >= $maxProducts) {
            throw ValidationException::withMessages([
                'category_id' => "Category has reached its maximum of {$maxProducts} products.",
            ]);
        }
    }

    private function syncVariants(Product $product, array $variants): void
    {
        foreach ($variants as $row) {
            ProductVariant::query()->create([
                'product_id' => $product->id,
                'type_key' => $row['type_key'],
                'type_value' => $row['type_value'],
                'price' => $row['price'],
                'stock' => (int) ($row['stock'] ?? 0),
            ]);
        }
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    private function storeUploadedImages(Product $product, array $files): void
    {
        $order = (int) $product->images()->max('sort_order');

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('products/'.$product->id, 'public');

            ProductImage::query()->create([
                'product_id' => $product->id,
                'disk' => 'public',
                'path' => $path,
                'sort_order' => ++$order,
            ]);
        }
    }
}
