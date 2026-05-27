<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\OrderStatus;
use Illuminate\Database\Seeder;

class StressTestSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::factory(10)->create();

        $products = Product::factory(500)
            ->recycle($categories)
            ->create();

        User::factory(200)
            ->create()
            ->each(function ($user, $index) use ($products) {
                $user->update([
                    'email'    => 'user' . ($index + 1) . '@example.com',
                    'password' => bcrypt('password'),
                ]);

                Order::factory(rand(1, 5))
                    ->create([
                        'user_id' => $user->id,
                        'status'  => OrderStatus::Paid,
                    ])
                    ->each(function ($order) use ($products) {
                        /** @var Order $order */
                        $sample = $products->random(rand(1, 4));
                        foreach ($sample as $product) {
                            OrderItem::factory()->create([
                                'order_id'   => $order->id,
                                'product_id' => $product->id,
                                'quantity'   => rand(1, 3),
                                'price'      => $product->price,
                            ]);
                        }
                    });

                $user->wishlists()->createMany(
                    $products->random(rand(0, 10))
                        ->map(fn ($p) => ['product_id' => $p->id])
                        ->toArray()
                );
            });
    }
}
