<?php

namespace App\Livewire;

use App\DTO\CartItemDTO;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CartDropdown extends Component
{
    protected $listeners = ['cart-updated' => '$refresh'];

    #[Computed]
    public function count(): int
    {
        if (Auth::check()) {
            $cart = Auth::user()->cart;

            return $cart ? $cart->items()->sum('quantity') : 0;
        }

        return collect(session('cart', []))->sum();
    }

    #[Computed]
    public function items(): array
    {
        return array_map(
            fn (CartItemDTO $item) => $item->toArray(),
            app(CartService::class)->getItems()
        );
    }

    public function render()
    {
        return view('livewire.cart-dropdown');
    }
}
