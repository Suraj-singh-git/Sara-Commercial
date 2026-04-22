<?php

namespace App\Services\Cart;

use App\Models\CartItem;
use App\Repositories\Contracts\CartRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function __construct(
        private readonly CartRepositoryInterface $cart,
    ) {}

    /**
     * @return Collection<int, CartItem>
     */
    public function items(?string $sessionId): Collection
    {
        $userId = Auth::id();

        return $this->cart->items($userId, $userId ? null : $sessionId);
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function itemsForRequest(Request $request): Collection
    {
        $userId = Auth::id();

        return $this->cart->items($userId, $userId ? null : $request->session()->getId());
    }

    public function add(?string $sessionId, int $variantId, int $quantity = 1): CartItem
    {
        $userId = Auth::id();

        return $this->cart->addOrUpdate($userId, $userId ? null : $sessionId, $variantId, $quantity);
    }

    public function addForRequest(Request $request, int $variantId, int $quantity = 1): CartItem
    {
        return $this->add($request->session()->getId(), $variantId, $quantity);
    }

    public function update(CartItem $item, int $quantity): void
    {
        $this->assertOwner($item);

        $this->cart->updateQuantity($item, $quantity);
    }

    public function remove(CartItem $item): void
    {
        $this->assertOwner($item);

        $this->cart->remove($item);
    }

    public function clear(?string $sessionId): void
    {
        $userId = Auth::id();

        $this->cart->clear($userId, $userId ? null : $sessionId);
    }

    public function clearForRequest(Request $request): void
    {
        $this->clear($request->session()->getId());
    }

    private function assertOwner(CartItem $item): void
    {
        $userId = Auth::id();

        if ($userId && $item->user_id === $userId) {
            return;
        }

        if (! $userId && $item->session_id === session()->getId()) {
            return;
        }

        abort(403);
    }
}
