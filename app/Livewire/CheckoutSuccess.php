<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;

class CheckoutSuccess extends Component
{
    public string $sessionId = '';

    public int $attempts = 0;

    public int $maxAttempts = 15;

    protected $queryString = [
        'sessionId' => ['as' => 'session_id'],
    ];

    public function checkOrder()
    {
        $this->attempts++;

        $order = Order::where(
            'stripe_session_id',
            $this->sessionId
        )->first();

        if ($order) {
            if ($order) {
                session()->flash(
                    'success',
                    'Payment successful! Your order has been created.'
                );

                return redirect()->route('orders');
            }
        }

        if ($this->attempts >= $this->maxAttempts) {
            session()->flash(
                'warning',
                'Payment succeeded, but order processing took longer than expected.'
            );

            return redirect()->route('orders');
        }
    }

    public function render()
    {
        return view('livewire.checkout-success');
    }
}
