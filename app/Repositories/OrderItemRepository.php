<?php

namespace App\Repositories;

use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Models\Order;
use App\Models\OrderItem;

class OrderItemRepository implements OrderItemRepositoryInterface
{
    public function createForOrder(Order $order, int $productId, int $quantity, float $price): void
    {
        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $productId,
            'quantity'   => $quantity,
            'price'      => $price,
        ]);
    }
}
