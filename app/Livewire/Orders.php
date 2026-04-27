<?php

namespace App\Livewire;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\DTO\OrderSummaryDTO;
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
        $orders = $this->orderRepo
            ->allForUser(Auth::user())
            ->map(fn ($order) => OrderSummaryDTO::fromModel($order));

        return view('livewire.orders', compact('orders'));
    }
}
