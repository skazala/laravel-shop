<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Models\Category;
use App\Payments\StripePaymentGateway;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StripeClient::class, function () {
            return new StripeClient(config('services.stripe.secret'));
        });

        $this->app->bind(PaymentGateway::class, StripePaymentGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('partials.header', function ($view) {
            $categories = Cache::remember('shop_categories', 3600, function () {
                return Category::orderBy('name')->get();
            });

            $view->with('categories', $categories);
        });
    }
}
