<?php

namespace App\Contracts;

use App\Models\User;

interface PaymentGateway
{
    public function createCheckoutSession(
        User $user,
        array $items,
        string $successUrl,
        string $cancelUrl
    ): string;
}
