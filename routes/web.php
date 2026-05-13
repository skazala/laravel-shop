<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StripeWebhookController;
use App\Livewire\Cart;
use App\Livewire\CheckoutSuccess;
use App\Livewire\Orders;
use App\Livewire\ProductDetail;
use App\Livewire\Products;
use App\Livewire\WishlistPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', Products::class)->name('products');
Route::get('/cart', Cart::class)->name('cart');
Route::get('/products/{id}', ProductDetail::class)->name('products.show');

Route::middleware('auth')->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'checkout'])
        ->name('checkout');
    Route::get('/orders', Orders::class)->name('orders');
    Route::get('/wishlist', WishlistPage::class)->name('wishlist');
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

if (app()->environment('local', 'testing')) {
    Route::middleware('auth')->post('/test/add-to-cart/{product}', function (
        \App\Models\Product $product,
        \App\Services\CartService $cartService
    ) {
        $cartService->add($product->id, Auth::user());
        return response()->json(['ok' => true]);
    })->name('test.add-to-cart');
    Route::post('/test/login', function (Request $request) {
        $user = \App\Models\User::where('email', $request->email)->firstOrFail();
        Auth::login($user);
        return response()->json(['ok' => true]);
    });
}

require __DIR__.'/auth.php';
