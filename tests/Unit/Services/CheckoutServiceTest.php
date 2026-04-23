<?php

namespace Tests\Unit\Services;

use App\Contracts\PaymentGateway;
use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Exceptions\InsufficientStockException;
use App\Jobs\LowStockJob;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_finalize_returns_early_if_session_already_exists(): void
    {
        $user = User::factory()->create();

        Order::factory()->create([
            'user_id' => $user->id,
            'stripe_session_id' => 'cs_test_123',
        ]);

        $orderRepo = Mockery::mock(OrderRepositoryInterface::class);
        $orderRepo->expects('existsByStripeSessionId')
            ->with('cs_test_123')
            ->andReturn(true);
        $orderRepo->shouldNotReceive('create');

        $service = new CheckoutService(
            Mockery::mock(PaymentGateway::class),
            $orderRepo,
            Mockery::mock(OrderItemRepositoryInterface::class),
        );

        $service->finalizePaidOrder([
            'stripe_session_id' => 'cs_test_123',
            'payment_intent'    => 'pi_test_123',
            'amount_total'      => 20000,
            'currency'          => 'usd',
            'user_id'           => $user->id,
        ]);

        $this->assertEquals(1, Order::count());
    }

    public function test_finalize_creates_order_and_clears_cart(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id'    => $category->id,
            'stock_quantity' => 10,
            'price'          => 100,
        ]);

        $cart = $user->cart()->create();
        $cart->items()->create([
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        app(CheckoutService::class)->finalizePaidOrder([
            'stripe_session_id' => 'cs_test_456',
            'payment_intent'    => 'pi_test_456',
            'amount_total'      => 20000,
            'currency'          => 'usd',
            'user_id'           => $user->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id'           => $user->id,
            'total_price'       => 200,
            'stripe_session_id' => 'cs_test_456',
            'status'            => 'paid',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'cart_id' => $cart->id,
        ]);

        $this->assertEquals(8, $product->fresh()->stock_quantity);
    }

    public function test_finalize_throws_when_stock_is_insufficient(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id'    => $category->id,
            'stock_quantity' => 1,
            'price'          => 100,
        ]);

        $cart = $user->cart()->create();
        $cart->items()->create([
            'product_id' => $product->id,
            'quantity'   => 5,
        ]);

        $this->expectException(InsufficientStockException::class);

        app(CheckoutService::class)->finalizePaidOrder([
            'stripe_session_id' => 'cs_test_789',
            'payment_intent'    => 'pi_test_789',
            'amount_total'      => 500,
            'currency'          => 'usd',
            'user_id'           => $user->id,
        ]);
    }

    public function test_low_stock_job_is_dispatched_when_stock_drops_below_threshold(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id'    => $category->id,
            'stock_quantity' => 5,
            'price'          => 100,
        ]);

        $cart = $user->cart()->create();
        $cart->items()->create([
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        app(CheckoutService::class)->finalizePaidOrder([
            'stripe_session_id' => 'cs_test_low',
            'payment_intent'    => 'pi_test_low',
            'amount_total'      => 100,
            'currency'          => 'usd',
            'user_id'           => $user->id,
        ]);

        Queue::assertPushed(LowStockJob::class);
    }
}
