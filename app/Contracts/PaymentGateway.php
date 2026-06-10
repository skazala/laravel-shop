<?php

namespace App\Contracts;

use App\Models\User;

interface PaymentGateway
{
    /**
     * @param array<int, array<string,mixed>> $items
     */
    public function createCheckoutSession(
        User $user,
        array $items,
        string $successUrl,
        string $cancelUrl
    ): string;
}
