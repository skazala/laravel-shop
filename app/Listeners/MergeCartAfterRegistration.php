<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Registered;

class MergeCartAfterRegistration
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function handle(Registered $event): void
    {
        $this->cartService->mergeSessionIntoUserCart($event->user);
    }
}
