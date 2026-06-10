<?php

namespace App\Livewire;

use App\DTO\CartItemDTO;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CartDropdown extends Component
{
    /** @var array<string, string> */
    protected $listeners = ['cart-updated' => '$refresh'];

    #[Computed]
    public function count(): int
    {
        if (Auth::check()) {
            $cart = Auth::user()->cart;

            return $cart ? $cart->items()->sum('quantity') : 0;
        }

        /** @var array<int,int> $sessionCart */
        $sessionCart = session('cart', []);

        return collect($sessionCart)->sum();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function items(): array
    {
        return array_map(
            fn (CartItemDTO $item) => $item->toArray(),
            app(CartService::class)->getItems()
        );
    }

    public function render(): View
    {
        return view('livewire.cart-dropdown');
    }
}
