<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CommerceSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('seed');
        Storage::disk('public')->makeDirectory('categories/menu');

        User::query()->updateOrCreate(
            ['email' => 'admin@sara.local'],
            [
                'name' => 'Sara Admin',
                'phone' => null,
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'buyer@sara.local'],
            [
                'name' => 'Sample Buyer',
                'phone' => '9990001111',
                'password' => Hash::make('password'),
                'role' => UserRole::Customer,
                'email_verified_at' => now(),
            ],
        );

        $tree = [
            [
                'name' => 'Industrial Machinery',
                'slug' => 'industrial-machinery',
                'sort' => 0,
                'image' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=600&q=80',
                'children' => [
                    [
                        'name' => 'Welding & Cutting',
                        'slug' => 'welding-cutting',
                        'sort' => 0,
                        'image' => 'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?auto=format&fit=crop&w=600&q=80',
                        'children' => [
                            ['name' => 'MIG Welders', 'slug' => 'mig-welders', 'sort' => 0, 'image' => 'https://images.unsplash.com/photo-1565043666747-69f6646db940?auto=format&fit=crop&w=600&q=80'],
                            ['name' => 'Plasma Cutters', 'slug' => 'plasma-cutters', 'sort' => 1, 'image' => 'https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?auto=format&fit=crop&w=600&q=80'],
                        ],
                    ],
                    [
                        'name' => 'Pneumatic Tools',
                        'slug' => 'pneumatic-tools',
                        'sort' => 1,
                        'image' => 'https://images.unsplash.com/photo-1530124566582-a618bc2615dc?auto=format&fit=crop&w=600&q=80',
                        'children' => [
                            ['name' => 'Impact Wrenches', 'slug' => 'impact-wrenches', 'sort' => 0, 'image' => 'https://images.unsplash.com/photo-1572981779307-38b8cabb2407?auto=format&fit=crop&w=600&q=80'],
                            ['name' => 'Air Compressors', 'slug' => 'air-compressors', 'sort' => 1, 'image' => 'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?auto=format&fit=crop&w=600&q=80'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Power Tools',
                'slug' => 'power-tools',
                'sort' => 1,
                'image' => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=600&q=80',
                'children' => [
                    [
                        'name' => 'Drilling',
                        'slug' => 'drilling',
                        'sort' => 0,
                        'image' => 'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?auto=format&fit=crop&w=600&q=80',
                        'children' => [
                            ['name' => 'Cordless Drills', 'slug' => 'cordless-drills', 'sort' => 0, 'image' => 'https://images.unsplash.com/photo-1572981779307-38b8cabb2407?auto=format&fit=crop&w=600&q=80'],
                            ['name' => 'Hammer Drills', 'slug' => 'hammer-drills', 'sort' => 1, 'image' => 'https://images.unsplash.com/photo-1581092160562-40aa08e66837?auto=format&fit=crop&w=600&q=80'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Hand Tools & Kits',
                'slug' => 'hand-tools-kits',
                'sort' => 2,
                'image' => 'https://images.unsplash.com/photo-1581148487289-03aeede2d622?auto=format&fit=crop&w=600&q=80',
                'children' => [
                    [
                        'name' => 'Measuring',
                        'slug' => 'measuring',
                        'sort' => 0,
                        'image' => 'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?auto=format&fit=crop&w=600&q=80',
                        'children' => [
                            ['name' => 'Digital Calipers', 'slug' => 'digital-calipers', 'sort' => 0, 'image' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=600&q=80'],
                        ],
                    ],
                ],
            ],
        ];

        $leafCategories = [];

        foreach ($tree as $root) {
            $rootModel = $this->upsertCategory($root, null);
            foreach ($root['children'] ?? [] as $child) {
                $childModel = $this->upsertCategory($child, $rootModel->id);
                foreach ($child['children'] ?? [] as $grand) {
                    $leaf = $this->upsertCategory($grand, $childModel->id);
                    $leafCategories[] = $leaf;
                }
            }
        }

        foreach ($leafCategories as $index => $category) {
            for ($p = 0; $p < 2; $p++) {
                $productName = $category->name.' Pro '.($p + 1);
                $slug = Str::slug($productName.'-'.$category->id);

                $product = Product::query()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'category_id' => $category->id,
                        'name' => $productName,
                        'short_description' => 'Industrial-grade '.$productName.' with warranty.',
                        'detailed_description' => "Specifications:\n- Heavy-duty build\n- Tested for workshop use\n- Ships from Sara Commercial fulfilment",
                        'is_visible' => true,
                    ],
                );

                $product->variants()->delete();
                $variantSeeds = [
                    ['type_key' => 'Model', 'type_value' => 'Standard', 'price' => 4999 + ($index * 200) + ($p * 150), 'stock' => 25],
                    ['type_key' => 'Model', 'type_value' => 'Pro', 'price' => 6999 + ($index * 200) + ($p * 150), 'stock' => 18],
                    ['type_key' => 'Warranty', 'type_value' => '1 Yr', 'price' => 0, 'stock' => 99],
                ];

                foreach ($variantSeeds as $row) {
                    ProductVariant::query()->create([
                        'product_id' => $product->id,
                        'type_key' => $row['type_key'],
                        'type_value' => $row['type_value'],
                        'price' => $row['price'],
                        'stock' => $row['stock'],
                    ]);
                }

                $product->images()->delete();
                for ($img = 0; $img < 3; $img++) {
                    $remote = 'https://picsum.photos/id/'.(20 + $index + $p + $img).'/900/700';
                    $file = $this->downloadImage($remote, "seed/prod-{$product->id}-{$img}.jpg");
                    if ($file) {
                        ProductImage::query()->create([
                            'product_id' => $product->id,
                            'disk' => 'public',
                            'path' => $file,
                            'sort_order' => $img,
                        ]);
                    }
                }
            }
        }

        $this->command->info('Seeded category tree, menu images, and catalog.');
    }

    /**
     * @param  array{name: string, slug: string, sort: int, image: string, children?: array}  $data
     */
    private function upsertCategory(array $data, ?int $parentId): Category
    {
        $menuPath = $this->downloadImage($data['image'], 'categories/menu/'.$data['slug'].'.jpg');

        return Category::query()->updateOrCreate(
            ['slug' => $data['slug']],
            [
                'parent_id' => $parentId,
                'name' => $data['name'],
                'max_products' => 25,
                'is_active' => true,
                'sort_order' => $data['sort'],
                'menu_image_path' => $menuPath,
            ],
        );
    }

    private function downloadImage(string $url, string $path): ?string
    {
        try {
            $response = Http::timeout(30)->retry(2, 200)->get($url);

            if (! $response->successful()) {
                return null;
            }

            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
