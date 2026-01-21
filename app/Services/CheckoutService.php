<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Jobs\LowStockJob;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    private const LOW_STOCK_THRESHOLD = 5;

    public function checkout(User $user): void
    {
        $cart = $user->cart()
            ->with('items.product')
            ->firstOrFail();

        if ($cart->items->isEmpty()) {
            throw new \LogicException('Cannot checkout an empty cart.');
        }

        DB::transaction(function () use ($cart, $user) {
            $total = 0;
            $lockedProducts = [];

            foreach ($cart->items as $item) {
                $product = Product::lockForUpdate()
                    ->findOrFail($item->product_id);

                if ($item->quantity > $product->stock_quantity) {
                    throw new InsufficientStockException(
                        "Not enough stock for {$product->name}"
                    );
                }

                $total += $item->quantity * $product->price;
                $lockedProducts[$product->id] = $product;
            }

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $total,
            ]);

            foreach ($cart->items as $item) {
                $product = $lockedProducts[$item->product_id];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'price' => $product->price,
                ]);

                $product->decrement('stock_quantity', $item->quantity);

                if ($product->stock_quantity < self::LOW_STOCK_THRESHOLD) {
                    LowStockJob::dispatch($product)->afterCommit();
                }
            }

            $cart->items()->delete();
        });
    }
}
