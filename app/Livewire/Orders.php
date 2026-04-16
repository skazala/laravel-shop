<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Orders extends Component
{
    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $orders = $user->orders()
            ->with('items.product')
            ->latest()
            ->get();

        return view('livewire.orders', compact('orders'));
    }
}
