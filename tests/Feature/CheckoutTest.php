<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_checkout_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'stock_quantity' => 10,
            'price' => 100,
        ]);

        $cart = $user->cart()->create();
        $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $service = app(CheckoutService::class);
        $service->checkout($user);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_price' => 200,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'cart_id' => $cart->id,
        ]);
    }

    public function test_guest_can_checkout_after_login(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 10,
            'price' => 100,
        ]);

        $this->withSession([
            'cart' => [
                $product->id => 2,
            ],
        ]);

        $response = $this->post(route('checkout'));
        $response->assertRedirect(route('login'));

        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->post(route('checkout'));

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_price' => 200,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->assertEmpty(session('cart'));
    }
}
