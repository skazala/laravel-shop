<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;

class Products extends Component
{
    public array $products = [];

    protected $listeners = [
        'cart-updated' => 'loadProducts',
    ];

    public function mount(CartService $cartService): void
    {
        $this->loadProducts($cartService);
    }

    public function loadProducts(CartService $cartService): void
    {
        $products = Product::query()
            ->select('id', 'name', 'price', 'stock_quantity')
            ->get();

        $cartItems = collect($cartService->getItems())
            ->groupBy(fn($item) => $item['product']->id)
            ->map(fn($items) => $items->sum('quantity'))
            ->toArray();

        $this->products = $products->map(function ($product) use ($cartItems) {
            $inCart = $cartItems[$product->id] ?? 0;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'stock_quantity' => $product->stock_quantity,
                'in_cart' => $inCart,
                'available_quantity' => max(
                    0,
                    $product->stock_quantity - $inCart
                ),
            ];
        })->toArray();
    }

    public function addToCart(int $productId): void
    {
        try {
            app(CartService::class)->add($productId);

            $this->dispatch('cart-updated');

            session()->flash('success', 'Product added to cart');
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.products');
    }
}
