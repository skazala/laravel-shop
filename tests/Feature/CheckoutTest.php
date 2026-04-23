<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_stripe_checkout(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 10,
            'price' => 100,
        ]);

        $cart = $user->cart()->create();
        $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->mock(CheckoutService::class, function ($mock) {
            $mock->shouldReceive('startStripeCheckout')
                ->once()
                ->andReturn('https://stripe.test/checkout');
        });

        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->post(route('checkout'));

        $response->assertRedirect('https://stripe.test/checkout');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_checkout_success_redirects_to_orders_when_order_exists(): void
    {
        $user = User::factory()->create();

        Order::factory()->create([
            'user_id' => $user->id,
            'stripe_session_id' => 'cs_test_123',
            'status' => 'paid',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\CheckoutSuccess::class, [
                'sessionId' => 'cs_test_123',
            ])
            ->call('checkOrder')
            ->assertRedirect(route('orders'));
    }

    public function test_checkout_with_empty_cart_redirects_back_with_error(): void
    {
        $user = User::factory()->create();

        $user->cart()->create();

        $this->mock(CheckoutService::class, function ($mock) {
            $mock->shouldReceive('startStripeCheckout')
                ->once()
                ->andReturn(null);
        });

        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->post(route('checkout'));

        $response->assertRedirect(route('cart'));
        $response->assertSessionHas('error', 'Your cart is empty.');
    }
}
