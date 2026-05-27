<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Registered;

class MergeCartAfterRegistration
{
    public function __construct(
        private CartService $cartService
    ) {
    }

    public function handle(Registered $event): void
    {
        /** @var \App\Models\User $user */
        $user = $event->user;

        $this->cartService->mergeSessionIntoUserCart($user);
    }
}
