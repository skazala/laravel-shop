<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;

class MergeSessionCartAfterLogin
{
    public function handle(Login $event): void
    {
        app(CartService::class)->mergeSessionIntoUserCart($event->user);
    }
}
