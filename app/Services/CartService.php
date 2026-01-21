<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function getCart(User $user): Cart
    {
        return $user->cart()->firstOrCreate([]);
    }

    public function add(int $productId, User $user): void
    {
        DB::transaction(function () use ($productId, $user) {
            $product = Product::lockForUpdate()->findOrFail($productId);

            if ($product->stock_quantity <= 0) {
                throw ValidationException::withMessages([
                    'product' => 'Product is out of stock.',
                ]);
            }

            $cart = $this->getCart($user);

            $item = $cart->items()->firstOrCreate(
                ['product_id' => $product->id],
                ['quantity' => 0]
            );

            if ($item->quantity >= $product->stock_quantity) {
                throw ValidationException::withMessages([
                    'product' => 'Not enough stock available.',
                ]);
            }

            $item->increment('quantity');
        });
    }
}
