<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@test.local',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Gadgets',
                'slug' => 'gadgets',
                'max_products' => 25,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'slug' => 'gadgets',
        ]);
    }

    public function test_customer_cannot_access_admin_categories(): void
    {
        $customer = User::factory()->create([
            'role' => UserRole::Customer,
        ]);

        $this->actingAs($customer)
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }
}
