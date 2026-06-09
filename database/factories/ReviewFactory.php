<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'product_id' => Product::factory(),
            'rating'     => $this->faker->numberBetween(1, 5),
            'comment'    => $this->faker->sentences(2, true),
        ];
    }
}
