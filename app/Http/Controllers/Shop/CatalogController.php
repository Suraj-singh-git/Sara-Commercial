<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\Category\CategoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly CategoryService $categoryService,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['category_id', 'min_price', 'max_price', 'q']);

        return view('shop.catalog', [
            'products' => $this->products->paginateVisible($filters),
            'categoryOptions' => $this->categoryService->parentSelectOptions(),
            'filters' => $filters,
        ]);
    }
}
