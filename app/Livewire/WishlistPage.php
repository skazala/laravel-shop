<?php

namespace App\Livewire;

use App\Services\CartService;
use App\Services\WishlistService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class WishlistPage extends Component
{
    protected $listeners = ['wishlist-updated' => '$refresh'];

    public function addToCart(int $productId): void
    {
        try {
            app(CartService::class)->add($productId, Auth::user());
            $this->dispatch('cart-updated');
            session()->flash('success', 'Product added to cart.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function remove(int $productId): void
    {
        app(WishlistService::class)->toggle(Auth::user(), $productId);
        $this->dispatch('wishlist-updated');
    }

    public function render()
    {
        $products = app(WishlistService::class)->getForUser(Auth::user());

        return view('livewire.wishlist-page', compact('products'));
    }
}
