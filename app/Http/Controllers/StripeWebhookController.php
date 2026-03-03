<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController
{
    public function __invoke(Request $request, CheckoutService $checkout)
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config('services.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException|UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid webhook'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $checkout->finalizePaidOrder([
                'stripe_session_id' => $session->id,
                'payment_intent' => $session->payment_intent,
                'amount_total' => $session->amount_total,
                'currency' => $session->currency,
                'user_id' => (int) $session->client_reference_id,
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
