<?php

namespace App\Providers;

use App\Listeners\MergeCartAfterRegistration;
use App\Listeners\MergeSessionCartAfterLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            MergeSessionCartAfterLogin::class,
        ],
        Registered::class => [
            MergeCartAfterRegistration::class,
        ],
    ];
}
