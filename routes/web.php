<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StripeWebhookController;
use App\Livewire\Products;
use App\Livewire\Cart;
use Illuminate\Support\Facades\Route;

Route::get('/', Products::class)->name('products');

Route::get('/cart', Cart::class)->name('cart');

Route::get(
    '/checkout/success',
    fn() =>
    view('checkout.success')
)->name('checkout.success');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::post('/checkout', [CheckoutController::class, 'checkout'])
    ->middleware('auth')
    ->name('checkout');

Route::post('/stripe/webhook', StripeWebhookController::class);

require __DIR__ . '/auth.php';
