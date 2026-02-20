<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StripeWebhookController;
use App\Livewire\CheckoutSuccess;
use App\Livewire\Products;
use App\Livewire\Cart;
use Illuminate\Support\Facades\Route;

Route::get('/', Products::class)->name('products');
Route::get('/cart', Cart::class)->name('cart');

Route::middleware('auth')->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'checkout'])
        ->name('checkout');
});
Route::get('/checkout/success', CheckoutSuccess::class)
    ->name('checkout.success');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::post('/stripe/webhook', StripeWebhookController::class);

require __DIR__ . '/auth.php';
