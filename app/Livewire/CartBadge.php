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
        if (! Auth::check()) {
            return 0;
        }

        return Auth::user()
            ->cart?->items()->sum('quantity') ?? 0;
    }

    public function render()
    {
        return view('livewire.cart-badge');
    }
}
