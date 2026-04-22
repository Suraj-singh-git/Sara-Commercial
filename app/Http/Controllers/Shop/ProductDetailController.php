<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\View\View;

class ProductDetailController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
    ) {}

    public function __invoke(string $slug): View
    {
        $product = $this->products->findBySlug($slug);

        abort_if(! $product || ! $product->is_visible, 404);

        return view('shop.product', compact('product'));
    }
}
