<?php

namespace App\Services;

use App\DTO\CartItemDTO;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    private const SESSION_CART_KEY = 'cart';

    private const TYPE_SESSION = 'session';

    private const TYPE_USER = 'user';

    public function add(int $productId, ?User $user = null): void
    {
        if ($user) {
            $this->addForUser($productId, $user);

            return;
        }

        $this->addToSession($productId);
    }

    public function count(): int
    {
        if (Auth::check()) {
            return Auth::user()
                ->cart?->items()->sum('quantity') ?? 0;
        }

        return array_sum(session(self::SESSION_CART_KEY, []));
    }

    public function getSessionItems(): array
    {
        return session(self::SESSION_CART_KEY, []);
    }

    public function clearSession(): void
    {
        session()->forget(self::SESSION_CART_KEY);
    }

    public function getCart(User $user): Cart
    {
        return $user->cart()->firstOrCreate([]);
    }

    protected function addForUser(int $productId, User $user): void
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

    protected function addToSession(int $productId): void
    {
        $product = Product::findOrFail($productId);

        if ($product->stock_quantity <= 0) {
            throw ValidationException::withMessages([
                'product' => 'Product is out of stock.',
            ]);
        }

        $cart = session(self::SESSION_CART_KEY, []);
        $currentQty = $cart[$productId] ?? 0;

        if ($currentQty >= $product->stock_quantity) {
            throw ValidationException::withMessages([
                'product' => 'Not enough stock available.',
            ]);
        }

        $cart[$productId] = $currentQty + 1;
        session([self::SESSION_CART_KEY => $cart]);
    }

    /**
     * @return CartItemDTO[]
     */
    public function getItems(): array
    {
        if (Auth::check()) {
            return $this->getItemsForUser(Auth::user());
        }

        return $this->getItemsFromSession();
    }

    protected function getItemsForUser(User $user): array
    {
        $cart = $user->cart()->with('items.product')->first();

        if (! $cart) {
            return [];
        }

        return $cart->items
            ->map(fn ($item) => CartItemDTO::forUserItem(
                cartItemId: $item->id,
                product: $item->product,
                quantity: $item->quantity,
            ))
            ->all();
    }

    protected function getItemsFromSession(): array
    {
        $sessionCart = session(self::SESSION_CART_KEY, []);

        if (empty($sessionCart)) {
            return [];
        }

        $products = Product::whereIn('id', array_keys($sessionCart))
            ->get()
            ->keyBy('id');

        return collect($sessionCart)
            ->map(function (int $qty, int $productId) use ($products) {
                if (! isset($products[$productId])) {
                    return null;
                }

                return CartItemDTO::forSessionItem(
                    productId: $productId,
                    product: $products[$productId],
                    quantity: $qty,
                );
            })
            ->filter()
            ->values()
            ->all();
    }

    public function updateQuantity(string $key, int $quantity): void
    {
        $quantity = max(1, $quantity);

        if (Auth::check()) {
            $item = CartItem::where('id', $key)
                ->whereHas('cart', fn ($q) => $q->where('user_id', Auth::id()))
                ->with('product')
                ->firstOrFail();
            $quantity = min($quantity, $item->product->stock_quantity);
            $item->update(['quantity' => $quantity]);

            return;
        }

        $cart = session(self::SESSION_CART_KEY, []);
        $productId = (int) str_replace('session-', '', $key);

        $product = Product::findOrFail($productId);
        $cart[$productId] = min($quantity, $product->stock_quantity);

        session([self::SESSION_CART_KEY => $cart]);
    }

    protected function parseKey(string $key): array
    {
        if (str_starts_with($key, 'session-')) {
            return ['type' => self::TYPE_SESSION, 'id' => (int) substr($key, 8)];
        }

        return ['type' => self::TYPE_USER, 'id' => (int) $key];
    }

    protected function removeForUser(int $cartItemId, User $user): void
    {
        CartItem::where('id', $cartItemId)
            ->whereHas('cart', fn ($q) => $q->where('user_id', $user->id))
            ->delete();
    }

    protected function removeFromSession(int $productId): void
    {
        $cart = session(self::SESSION_CART_KEY, []);
        unset($cart[$productId]);
        session([self::SESSION_CART_KEY => $cart]);
    }

    public function remove(string $key): void
    {
        $parsed = $this->parseKey($key);

        if ($parsed['type'] === 'user' && Auth::check()) {
            $this->removeForUser($parsed['id'], Auth::user());

            return;
        }

        if ($parsed['type'] === 'session') {
            $this->removeFromSession($parsed['id']);
        }
    }

    public function mergeSessionIntoUserCart(User $user): void
    {
        $sessionCart = session(self::SESSION_CART_KEY, []);

        if (empty($sessionCart)) {
            return;
        }

        DB::transaction(function () use ($sessionCart, $user) {
            $cart = $this->getCart($user);

            $products = Product::whereIn('id', array_keys($sessionCart))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($sessionCart as $productId => $qtyFromSession) {
                if (! isset($products[$productId])) {
                    continue;
                }

                $product = $products[$productId];

                $item = $cart->items()->firstOrCreate(
                    ['product_id' => $productId],
                    ['quantity' => 0]
                );

                $maxAddable = max(
                    0,
                    $product->stock_quantity - $item->quantity
                );

                if ($maxAddable <= 0) {
                    continue;
                }

                $item->increment(
                    'quantity',
                    min($qtyFromSession, $maxAddable)
                );
            }
        });

        $this->clearSession();
    }

    /**
     * @return array<int, int> [productId => quantity]
     */
    public function quantitiesByProductId(): array
    {
        return collect($this->getItems())
            ->map(fn (CartItemDTO $item) => [
                'product_id' => $item->product->id,
                'quantity' => $item->quantity,
            ])
            ->groupBy('product_id')
            ->map(fn ($items) => $items->sum('quantity'))
            ->toArray();
    }
}
