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

    public function mount(): void
    {
        $this->loadProducts();
    }

    public function loadProducts(): void
    {
        $products = Product::query()
            ->select('id', 'name', 'price', 'stock_quantity')
            ->get();

        $cartItems = Auth::check()
            ? Auth::user()
                ->cart
                ?->items()
                ->pluck('quantity', 'product_id')
                ->toArray()
            : [];

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
        if (! Auth::check()) {
            $this->redirectRoute('login');
            return;
        }

        try {
            app(CartService::class)->add($productId, Auth::user());

            $this->updateLocalAvailability($productId);
            $this->dispatch('cart-updated');

            session()->flash('success', 'Product added to cart');
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    protected function updateLocalAvailability(int $productId): void
    {
        $this->products = array_map(function ($product) use ($productId) {
            if ($product['id'] === $productId) {
                $product['in_cart']++;
                $product['available_quantity'] = max(
                    0,
                    $product['stock_quantity'] - $product['in_cart']
                );
            }

            return $product;
        }, $this->products);
    }

    public function render()
    {
        return view('livewire.products');
    }
}
