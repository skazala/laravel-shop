<?php

namespace App\DTO;

use App\Models\Product;

final class CartItemDTO
{
    public function __construct(
        public readonly string $key,
        public readonly Product $product,
        public readonly string $name,
        public readonly int $price,
        public readonly int $quantity,
        public readonly int $maxQuantity,
    ) {}

    public static function forUserItem(
        int $cartItemId,
        Product $product,
        int $quantity
    ): self {
        return new self(
            key: (string) $cartItemId,
            product: $product,
            name: $product->name,
            price: $product->price,
            quantity: $quantity,
            maxQuantity: $product->stock_quantity,
        );
    }

    public static function forSessionItem(
        int $productId,
        Product $product,
        int $quantity
    ): self {
        return new self(
            key: 'session-' . $productId,
            product: $product,
            name: $product->name,
            price: $product->price,
            quantity: $quantity,
            maxQuantity: $product->stock_quantity,
        );
    }

    public function total(): int
    {
        return $this->price * $this->quantity;
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'maxQuantity' => $this->maxQuantity,
        ];
    }
}
