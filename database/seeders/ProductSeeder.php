<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->catalog() as $categorySlug => $products) {

            $category = Category::where('slug', $categorySlug)->first();

            if (! $category) {
                $this->command->error("Missing category: {$categorySlug}");
                continue;
            }

            foreach ($products as $name) {

                Product::firstOrCreate(
                    [
                        'name' => $name,
                    ],
                    [
                        'price' => fake()->randomFloat(2, 5, 80),
                        'stock_quantity' => fake()->numberBetween(5, 60),
                        'category_id' => $category->id,
                    ]
                );
            }
        }
    }

    private function catalog(): array
    {
        return [
            'yarn' => [
                'Merino Soft Yarn',
                'Alpaca Cloud Yarn',
                'Cotton Summer Yarn',
                'Chunky Wool Yarn',
                'Bamboo Silk Blend Yarn',
                'Mohair Lace Yarn',
                'Sock Wool Yarn',
                'Organic Cotton Yarn',
            ],

            'needles-hooks' => [
                'Circular Knitting Needles 4mm',
                'Circular Knitting Needles 6mm',
                'Bamboo Knitting Needles 5mm',
                'Aluminum Crochet Hook 3mm',
                'Ergonomic Crochet Hook 5mm',
                'Interchangeable Needle Set',
            ],

            'patterns-books' => [
                'Modern Knitting Patterns Book',
                'Beginner Crochet Guide',
                'Embroidery on Knits Handbook',
            ],

            'kits' => [
                'Beginner Knitting Kit',
                'Crochet Starter Kit',
            ],

            'accessories' => [
                'Knitting Stitch Markers',
                'Yarn Ball Winder',
                'Project Bag for Knitting',
                'Row Counter',
                'Blocking Mats Set',
            ],
        ];
    }
}
