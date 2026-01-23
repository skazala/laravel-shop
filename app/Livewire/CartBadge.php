<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CartBadge extends Component
{
    protected $listeners = ['cart-updated' => '$refresh'];

    #[Computed]
    public function count(): int
    {
        $user = Auth::user();

        if (! $user || ! $user->cart) {
            return 0;
        }

        return $user->cart->items->sum('quantity');
    }

    public function render()
    {
        return view('livewire.cart-badge');
    }
}
