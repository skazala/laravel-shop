<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function checkout(CheckoutService $checkoutService)
    {
        $checkoutService->checkout(Auth::user());

        return redirect()->route('products')
            ->with('success', 'Order placed successfully!');
    }
}
