<?php

namespace App\DTO;

final class FinalizeOrderDTO
{
    public function __construct(
        public readonly string $stripeSessionId,
        public readonly string $paymentIntent,
        public readonly int $amountTotal,
        public readonly string $currency,
        public readonly int $userId,
    ) {
    }

    public static function fromStripeWebhook(object $session): self
    {
        return new self(
            stripeSessionId: $session->id,
            paymentIntent: $session->payment_intent,
            amountTotal: $session->amount_total,
            currency: $session->currency,
            userId: (int) $session->client_reference_id,
        );
    }
}
