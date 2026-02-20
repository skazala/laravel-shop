<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;

class MergeSessionCartAfterLogin
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function handle(Login $event): void
    {
        $this->cartService->mergeSessionIntoUserCart($event->user);
    }
}
