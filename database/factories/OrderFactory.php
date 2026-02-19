<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'total_price' => 200,
            'status' => OrderStatus::Paid,
            'stripe_session_id' => 'cs_test_' . $this->faker->uuid,
            'stripe_payment_intent_id' => 'pi_test_' . $this->faker->uuid,
            'stripe_amount_total' => 20000,
            'currency' => 'usd',
        ];
    }
}
