<?php

namespace App\Payments;

use App\Contracts\PaymentGateway;
use App\Models\User;
use Stripe\StripeClient;

class StripePaymentGateway implements PaymentGateway
{
    public function __construct(
        private StripeClient $stripe
    ) {}

    public function createCheckoutSession(
        User $user,
        array $items,
        string $successUrl,
        string $cancelUrl
    ): string {
        $session = $this->stripe->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => $items,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => $user->id,
        ]);

        return $session->url;
    }
}
