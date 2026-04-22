<?php

namespace App\Contracts\Repositories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface
{
    public function allForUser(User $user): Collection;
    public function existsByStripeSessionId(string $sessionId): bool;
    public function create(array $data): Order;
}
