<?php

namespace App\DTO;

use App\Models\OrderItem;

final class OrderItemDTO
{
    public function __construct(
        public readonly string $productName,
        public readonly int $quantity,
        public readonly float $price,
    ) {
    }

    public static function fromModel(OrderItem $item): self
    {
        return new self(
            productName: $item->product->name,
            quantity: $item->quantity,
            price: (float) $item->price,
        );
    }

    public function subtotal(): float
    {
        return $this->price * $this->quantity;
    }
}
