<?php

namespace App\Livewire;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\DTO\OrderSummaryDTO;
use App\Jobs\OrderDeliveredJob;
use App\Jobs\OrderShippedJob;
use App\Models\Order;
use App\OrderStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Orders extends Component
{
    protected OrderRepositoryInterface $orderRepo;

    public function boot(OrderRepositoryInterface $orderRepo): void
    {
        $this->orderRepo = $orderRepo;
    }

    public function markShipped(int $orderId): void
    {
        $this->authorizeAdmin();
        $order = Order::findOrFail($orderId);
        $this->orderRepo->updateStatus($order, OrderStatus::Shipped);
        OrderShippedJob::dispatch($order->load('user', 'items.product'));
    }

    public function markDelivered(int $orderId): void
    {
        $this->authorizeAdmin();
        $order = Order::findOrFail($orderId);
        $this->orderRepo->updateStatus($order, OrderStatus::Delivered);
        OrderDeliveredJob::dispatch($order->load('user', 'items.product'));
    }

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::user()?->is_admin, 403);
    }

    public function render()
    {
        $orders = $this->orderRepo
            ->allForUser(Auth::user())
            ->map(fn ($order) => OrderSummaryDTO::fromModel($order));

        return view('livewire.orders', compact('orders'));
    }
}
