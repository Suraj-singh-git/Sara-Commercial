<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
    ) {}

    public function __invoke(): View
    {
        return view('shop.home', [
            'featured' => $this->products->featured(8),
        ]);
    }
}
