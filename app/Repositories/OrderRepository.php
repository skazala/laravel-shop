<?php

namespace App\Repositories;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository implements OrderRepositoryInterface
{
    public function allForUser(User $user): Collection
    {
        return $user->orders()
            ->with('items.product')
            ->latest()
            ->get();
    }

    public function existsByStripeSessionId(string $sessionId): bool
    {
        return Order::where('stripe_session_id', $sessionId)->exists();
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }
}
