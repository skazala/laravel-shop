<?php

namespace App\Contracts\Repositories;

use App\Models\Order;

interface OrderItemRepositoryInterface
{
    public function createForOrder(Order $order, int $productId, int $quantity, float $price): void;
}
