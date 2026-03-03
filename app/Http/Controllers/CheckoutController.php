<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function checkout(CheckoutService $checkoutService)
    {
        $url = $checkoutService->startStripeCheckout(Auth::user());

        if (! $url) {
            return redirect()->route('cart')
                ->with('error', 'Your cart is empty.');
        }

        return redirect()->away($url);
    }
}
