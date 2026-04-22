<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_lists_visible_products(): void
    {
        $category = Category::query()->create([
            'name' => 'Test',
            'slug' => 'test',
            'max_products' => 25,
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Demo',
            'slug' => 'demo',
            'short_description' => 'Short',
            'detailed_description' => 'Long',
            'is_visible' => true,
        ]);

        ProductVariant::query()->create([
            'product_id' => $product->id,
            'type_key' => 'Size',
            'type_value' => 'M',
            'price' => 100,
            'stock' => 5,
        ]);

        $this->get(route('shop.catalog'))
            ->assertOk()
            ->assertSee('Demo');
    }

    public function test_catalog_from_price_ignores_zero_priced_addon_variants(): void
    {
        $category = Category::query()->create([
            'name' => 'Cat',
            'slug' => 'cat',
            'max_products' => 25,
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Widget',
            'slug' => 'widget',
            'short_description' => 'Short',
            'detailed_description' => 'Long',
            'is_visible' => true,
        ]);

        ProductVariant::query()->create([
            'product_id' => $product->id,
            'type_key' => 'Warranty',
            'type_value' => '1 Yr',
            'price' => 0,
            'stock' => 99,
        ]);
        ProductVariant::query()->create([
            'product_id' => $product->id,
            'type_key' => 'Model',
            'type_value' => 'Std',
            'price' => 1250,
            'stock' => 5,
        ]);

        $this->get(route('shop.catalog'))
            ->assertOk()
            ->assertSee('From ₹1,250.00')
            ->assertDontSee('From ₹0.00');
    }

    public function test_catalog_price_range_requires_single_variant_to_match_range(): void
    {
        $category = Category::query()->create([
            'name' => 'Tools',
            'slug' => 'tools',
            'max_products' => 25,
            'is_active' => true,
        ]);

        // Product A should be excluded for max=1000 (priced variants are above 1000).
        $productA = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'High Range Drill',
            'slug' => 'high-range-drill',
            'short_description' => 'Short',
            'detailed_description' => 'Long',
            'is_visible' => true,
        ]);
        ProductVariant::query()->create([
            'product_id' => $productA->id,
            'type_key' => 'Warranty',
            'type_value' => '1 Yr',
            'price' => 0,
            'stock' => 99,
        ]);
        ProductVariant::query()->create([
            'product_id' => $productA->id,
            'type_key' => 'Model',
            'type_value' => 'Pro',
            'price' => 3500,
            'stock' => 5,
        ]);

        // Product B should match the range.
        $productB = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Budget Drill',
            'slug' => 'budget-drill',
            'short_description' => 'Short',
            'detailed_description' => 'Long',
            'is_visible' => true,
        ]);
        ProductVariant::query()->create([
            'product_id' => $productB->id,
            'type_key' => 'Model',
            'type_value' => 'Basic',
            'price' => 899,
            'stock' => 5,
        ]);

        $this->get(route('shop.catalog', ['min_price' => 1, 'max_price' => 1000]))
            ->assertOk()
            ->assertSee('Budget Drill')
            ->assertDontSee('High Range Drill');
    }
}
