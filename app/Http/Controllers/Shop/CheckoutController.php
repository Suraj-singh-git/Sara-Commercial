<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\Cart\CartService;
use App\Services\Checkout\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout,
    ) {}

    public function create(Request $request): View
    {
        return view('shop.checkout', [
            'items' => $this->cart->itemsForRequest($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $address = $request->validate([
            'contact_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:32'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['required', 'string', 'max:32'],
            'country' => ['nullable', 'string', 'max:2'],
            'payment_mode' => ['required', 'in:full_online,partial_cod,cod'],
        ]);

        $order = $this->checkout->placeOrder($request->session()->getId(), $request->string('payment_mode')->toString(), $address);

        return redirect()->route('account.orders.show', $order->id)->with('status', 'Order placed successfully.');
    }
}
