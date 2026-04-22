<?php

namespace App\Livewire;

use App\Contracts\Repositories\OrderRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Orders extends Component
{
    protected OrderRepositoryInterface $orderRepo;

    public function boot(OrderRepositoryInterface $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    public function render()
    {
        $orders = $this->orderRepo->allForUser(Auth::user());

        return view('livewire.orders', compact('orders'));
    }
}
