<?php

namespace App\Livewire;

use App\Services\WishlistService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class WishlistButton extends Component
{
    public int $productId;
    public bool $wishlisted = false;

    public function mount(int $productId): void
    {
        $this->productId = $productId;

        if (Auth::check()) {
            $this->wishlisted = app(WishlistService::class)
                ->isWishlisted(Auth::user(), $productId);
        }
    }

    public function toggle(): void
    {
        if (!Auth::check()) {
            $this->redirectRoute('login');
            return;
        }

        $this->wishlisted = app(WishlistService::class)
            ->toggle(Auth::user(), $this->productId);

        $this->dispatch('wishlist-updated');
    }

    public function render(): View
    {
        return view('livewire.wishlist-button');
    }
}
