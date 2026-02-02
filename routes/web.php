<?php

use App\Livewire\Products;
use App\Livewire\Cart;
use Illuminate\Support\Facades\Route;

Route::get('/', Products::class)->name('products');

Route::get('/cart', Cart::class)->name('cart');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
