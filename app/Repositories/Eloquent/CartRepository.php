<?php

namespace App\Repositories\Eloquent;

use App\Models\CartItem;
use App\Repositories\Contracts\CartRepositoryInterface;
use Illuminate\Support\Collection;

class CartRepository implements CartRepositoryInterface
{
    public function items(?int $userId, ?string $sessionId): Collection
    {
        $query = CartItem::query()->with(['variant.product.images']);

        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        } else {
            return collect();
        }

        return $query->get();
    }

    public function addOrUpdate(?int $userId, ?string $sessionId, int $variantId, int $quantity): CartItem
    {
        $query = CartItem::query()->where('product_variant_id', $variantId);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        $existing = $query->first();

        if ($existing) {
            $existing->update(['quantity' => $existing->quantity + $quantity]);

            return $existing->fresh(['variant.product.images']);
        }

        return CartItem::query()->create([
            'user_id' => $userId,
            'session_id' => $userId ? null : $sessionId,
            'product_variant_id' => $variantId,
            'quantity' => $quantity,
        ]);
    }

    public function updateQuantity(CartItem $item, int $quantity): void
    {
        if ($quantity < 1) {
            $item->delete();

            return;
        }

        $item->update(['quantity' => $quantity]);
    }

    public function remove(CartItem $item): void
    {
        $item->delete();
    }

    public function clear(?int $userId, ?string $sessionId): void
    {
        $query = CartItem::query();

        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        } else {
            return;
        }

        $query->delete();
    }
}
