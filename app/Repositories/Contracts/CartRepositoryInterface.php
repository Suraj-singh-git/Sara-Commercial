<?php

namespace App\Repositories\Contracts;

use App\Models\CartItem;
use Illuminate\Support\Collection;

interface CartRepositoryInterface
{
    /**
     * @return Collection<int, CartItem>
     */
    public function items(?int $userId, ?string $sessionId): Collection;

    public function addOrUpdate(?int $userId, ?string $sessionId, int $variantId, int $quantity): CartItem;

    public function updateQuantity(CartItem $item, int $quantity): void;

    public function remove(CartItem $item): void;

    public function clear(?int $userId, ?string $sessionId): void;
}
