<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyNowFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_buy_now_adds_selected_variant_and_redirects_to_checkout(): void
    {
        $user = User::factory()->create();

        $category = Category::query()->create([
            'name' => 'Cutting Tools',
            'slug' => 'cutting-tools',
            'max_products' => 25,
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Plasma Unit',
            'slug' => 'plasma-unit',
            'short_description' => 'Short',
            'detailed_description' => 'Long',
            'is_visible' => true,
        ]);

        $variant = ProductVariant::query()->create([
            'product_id' => $product->id,
            'type_key' => 'Model',
            'type_value' => 'Pro',
            'price' => 7999,
            'stock' => 10,
        ]);

        $this->actingAs($user)
            ->post(route('shop.cart.add'), [
                'product_variant_id' => $variant->id,
                'quantity' => 2,
                'buy_now' => 1,
            ])
            ->assertRedirect(route('shop.checkout'));

        $this->get(route('shop.checkout'))
            ->assertOk()
            ->assertSee('Plasma Unit')
            ->assertSee('Qty 2');
    }
}

