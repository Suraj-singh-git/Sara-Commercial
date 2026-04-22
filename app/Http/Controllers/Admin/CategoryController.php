<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Category\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService,
    ) {}

    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => $this->categoryService->paginate(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create', [
            'parentOptions' => $this->categoryService->parentSelectOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'parent_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:160', 'unique:categories,slug'],
            'max_products' => ['required', 'integer', 'min:1', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['nullable', 'boolean'],
            'menu_image' => ['nullable', 'image', 'max:4096'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('menu_image')) {
            $data['menu_image_path'] = $request->file('menu_image')->store('categories/menu', 'public');
        }

        unset($data['menu_image']);

        $this->categoryService->create($data);

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'parentOptions' => $this->categoryService->parentSelectOptions($category),
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'parent_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:160', 'unique:categories,slug,'.$category->id],
            'max_products' => ['required', 'integer', 'min:1', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['nullable', 'boolean'],
            'menu_image' => ['nullable', 'image', 'max:4096'],
            'remove_menu_image' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if ($request->boolean('remove_menu_image') && $category->menu_image_path) {
            Storage::disk('public')->delete($category->menu_image_path);
            $data['menu_image_path'] = null;
        }

        if ($request->hasFile('menu_image')) {
            if ($category->menu_image_path) {
                Storage::disk('public')->delete($category->menu_image_path);
            }
            $data['menu_image_path'] = $request->file('menu_image')->store('categories/menu', 'public');
        }

        unset($data['menu_image'], $data['remove_menu_image']);

        $this->categoryService->update($category, $data);

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $path = $category->menu_image_path;

        $this->categoryService->delete($category);

        if ($path) {
            Storage::disk('public')->delete($path);
        }

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted.');
    }
}
