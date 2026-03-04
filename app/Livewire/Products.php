<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Products extends Component
{
    use WithPagination;

    protected $listeners = [
        'cart-updated' => '$refresh',
    ];

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

    public function render(CartService $cartService)
    {
        $products = Product::query()
            ->select('id', 'name', 'price', 'stock_quantity')
            ->paginate(10);

        $quantitiesInCart = $cartService->quantitiesByProductId();

        $products->getCollection()->transform(function (Product $product) use ($quantitiesInCart) {
            $inCart = $quantitiesInCart[$product->id] ?? 0;

            $product->in_cart = $inCart;
            $product->available_quantity = max(
                0,
                $product->stock_quantity - $inCart
            );

            return $product;
        });

        return view('livewire.products', compact('products'));
    }
}
