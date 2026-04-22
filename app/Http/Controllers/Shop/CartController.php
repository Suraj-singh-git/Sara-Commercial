<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Services\Cart\CartService;
use App\Services\Notification\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly NotificationService $notifications,
    ) {}

    public function index(Request $request): View
    {
        return view('shop.cart', [
            'items' => $this->cart->itemsForRequest($request),
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:50'],
            'buy_now' => ['nullable', 'boolean'],
        ]);

        $item = $this->cart->addForRequest($request, (int) $data['product_variant_id'], (int) ($data['quantity'] ?? 1));

        if ($request->user()) {
            $productName = (string) ($item->variant?->product?->name ?? 'Product');
            $this->notifications->productAddedToCart($request->user(), $productName, (int) ($data['quantity'] ?? 1));
        }

        if ($request->boolean('buy_now')) {
            if (! $request->user()) {
                return redirect()->route('login');
            }

            return redirect()->route('shop.checkout');
        }

        return back()->with('status', 'Added to cart.');
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $this->cart->update($cartItem, (int) $data['quantity']);

        return back()->with('status', 'Cart updated.');
    }

    public function remove(CartItem $cartItem): RedirectResponse
    {
        $this->cart->remove($cartItem);

        return back()->with('status', 'Item removed.');
    }
}
