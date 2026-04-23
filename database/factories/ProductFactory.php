<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $products = [
            'Merino Soft Yarn',
            'Alpaca Cloud Yarn',
            'Cotton Summer Yarn',
            'Chunky Wool Yarn',
            'Bamboo Silk Blend Yarn',
            'Mohair Lace Yarn',
            'Sock Wool Yarn',
            'Organic Cotton Yarn',
            'Circular Knitting Needles 4mm',
            'Circular Knitting Needles 6mm',
            'Bamboo Knitting Needles 5mm',
            'Aluminum Crochet Hook 3mm',
            'Ergonomic Crochet Hook 5mm',
            'Interchangeable Needle Set',
            'Knitting Stitch Markers',
            'Yarn Ball Winder',
            'Project Bag for Knitting',
            'Row Counter',
            'Blocking Mats Set',
        ];

        return [
            'name' => $this->faker->randomElement($products),
            'price' => $this->faker->randomFloat(2, 5, 80),
            'stock_quantity' => $this->faker->numberBetween(5, 60),
            'category_id' => Category::factory(),
        ];
    }
}
