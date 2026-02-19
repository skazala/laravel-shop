<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function checkout(CheckoutService $checkoutService)
    {
        $url = $checkoutService->startStripeCheckout(Auth::user());

        return redirect()->away($url);
    }
}
