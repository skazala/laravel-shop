<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

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

        $quantitiesInCart = $cartService->quantitiesByProductId();

        $this->products = $products
            ->map(fn (Product $product) => $this->mapProduct(
                $product,
                $quantitiesInCart[$product->id] ?? 0
            ))
            ->toArray();
    }

    protected function mapProduct(Product $product, int $inCart): array
    {
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
    }

    public function addToCart(int $productId): void
    {
        try {
            app(CartService::class)->add($productId, Auth::user());

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
