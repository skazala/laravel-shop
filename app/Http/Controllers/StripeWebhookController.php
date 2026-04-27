<?php

namespace App\Http\Controllers;

use App\DTO\FinalizeOrderDTO;
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

            $checkout->finalizePaidOrder(
                FinalizeOrderDTO::fromStripeWebhook($session),
            );
        }

        return response()->json(['ok' => true]);
    }
}
