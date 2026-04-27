<?php

namespace App\DTO;

use App\Models\Order;
use Illuminate\Support\Collection;

final class OrderSummaryDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $status,
        public readonly float $totalPrice,
        public readonly string $currency,
        public readonly string $createdAt,
        public readonly Collection $items,
    ) {
    }

    public static function fromModel(Order $order): self
    {
        return new self(
            id: $order->id,
            status: $order->status->value,
            totalPrice: (float) $order->total_price,
            currency: $order->currency,
            createdAt: $order->created_at->format('M j, Y'),
            items: $order->items->map(fn ($item) => OrderItemDTO::fromModel($item)),
        );
    }
}
