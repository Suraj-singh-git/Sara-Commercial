<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\Category\CategoryService;
use App\Services\Product\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly CategoryRepositoryInterface $categories,
        private readonly CategoryService $categoryService,
        private readonly ProductService $productService,
    ) {}

    public function index(): View
    {
        return view('admin.products.index', [
            'products' => $this->products->paginateForAdmin(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'categoryOptions' => $this->categoryService->parentSelectOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', 'unique:products,slug'],
            'short_description' => ['required', 'string', 'max:500'],
            'detailed_description' => ['required', 'string'],
            'is_visible' => ['nullable', 'boolean'],
            'variants' => ['required', 'array', 'min:1', 'max:6'],
            'variants.*.type_key' => ['required', 'string', 'max:60'],
            'variants.*.type_value' => ['required', 'string', 'max:120'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'images.*' => ['nullable', 'image', 'max:4096'],
        ]);

        $this->productService->create(
            [
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'slug' => $data['slug'] ?? null,
                'short_description' => $data['short_description'],
                'detailed_description' => $data['detailed_description'],
                'is_visible' => $request->boolean('is_visible', true),
            ],
            $data['variants'],
            $request->file('images', [])
        );

        return redirect()->route('admin.products.index')->with('status', 'Product created.');
    }

    public function edit(Product $product): View
    {
        $product->load(['variants', 'images', 'category']);

        return view('admin.products.edit', [
            'product' => $product,
            'categoryOptions' => $this->categoryService->parentSelectOptions(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', 'unique:products,slug,'.$product->id],
            'short_description' => ['required', 'string', 'max:500'],
            'detailed_description' => ['required', 'string'],
            'is_visible' => ['nullable', 'boolean'],
            'variants' => ['required', 'array', 'min:1', 'max:6'],
            'variants.*.type_key' => ['required', 'string', 'max:60'],
            'variants.*.type_value' => ['required', 'string', 'max:120'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'images.*' => ['nullable', 'image', 'max:4096'],
        ]);

        $this->productService->update(
            $product,
            [
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'slug' => $data['slug'] ?? null,
                'short_description' => $data['short_description'],
                'detailed_description' => $data['detailed_description'],
                'is_visible' => $request->boolean('is_visible', true),
            ],
            $data['variants'],
            $request->file('images', [])
        );

        return redirect()->route('admin.products.index')->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->productService->delete($product);

        return redirect()->route('admin.products.index')->with('status', 'Product removed.');
    }
}
