<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;

class CheckoutSuccess extends Component
{
    public string $sessionId = '';

    public int $attempts = 0;

    public int $maxAttempts = 15;

    /** @var array<string, array{as?: string}> */
    protected $queryString = [
        'sessionId' => ['as' => 'session_id'],
    ];

    public function checkOrder(): RedirectResponse
    {
        $this->attempts++;

        $order = Order::where(
            'stripe_session_id',
            $this->sessionId
        )->first();

        if ($order) {
            session()->flash(
                'success',
                'Payment successful! Your order has been created.'
            );

            return redirect()->route('orders');
        }

        if ($this->attempts >= $this->maxAttempts) {
            session()->flash(
                'warning',
                'Payment succeeded, but order processing took longer than expected.'
            );

            return redirect()->route('orders');
        }

        session()->flash(
            'error',
            'Something went wrong while processing your order. We are checking for updates...'
        );
        Log::warning(
            "Order not found for session ID {$this->sessionId} (attempt {$this->attempts}/{$this->maxAttempts})"
        );

        return redirect()->route('orders');
    }

    public function render(): View
    {
        return view('livewire.checkout-success');
    }
}
