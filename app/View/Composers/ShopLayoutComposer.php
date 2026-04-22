<?php

namespace App\View\Composers;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Services\Cart\CartService;
use Illuminate\View\View;

class ShopLayoutComposer
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
        private readonly CartService $cart,
    ) {}

    public function compose(View $view): void
    {
        $items = $this->cart->items(session()->getId());

        $view->with('menuCategories', $this->categories->menuTree());
        $view->with('cartCount', (int) $items->sum('quantity'));
    }
}
