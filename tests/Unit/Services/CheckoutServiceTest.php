<?php

namespace Tests\Unit\Services;

use App\Contracts\PaymentGateway;
use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\DTO\FinalizeOrderDTO;
use App\Exceptions\InsufficientStockException;
use App\Jobs\LowStockJob;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param array{
     *     stripe_session_id?: string,
     *     payment_intent?: string,
     *     amount_total?: int,
     *     currency?: string,
     *     user_id?: int
     * } $overrides
     */
    private function makeDto(array $overrides = []): FinalizeOrderDTO
    {
        return new FinalizeOrderDTO(
            stripeSessionId: $overrides['stripe_session_id'] ?? 'cs_test_123',
            paymentIntent:   $overrides['payment_intent']   ?? 'pi_test_123',
            amountTotal:     $overrides['amount_total']     ?? 20000,
            currency:        $overrides['currency']         ?? 'usd',
            userId:          $overrides['user_id']          ?? 1,
        );
    }

    public function test_finalize_returns_early_if_session_already_exists(): void
    {
        $user = User::factory()->create();

        Order::factory()->create([
            'user_id' => $user->id,
            'stripe_session_id' => 'cs_test_123',
        ]);

        /** @var OrderRepositoryInterface&\Mockery\MockInterface $orderRepo */
        $orderRepo = Mockery::mock(OrderRepositoryInterface::class);
        $orderRepo->shouldReceive('existsByStripeSessionId')
            ->once()
            ->with('cs_test_123')
            ->andReturn(true);
        $orderRepo->shouldNotReceive('create');

        /** @var OrderItemRepositoryInterface&\Mockery\MockInterface $orderItemRepo */
        $orderItemRepo = Mockery::mock(OrderItemRepositoryInterface::class);

        $service = new CheckoutService(
            Mockery::mock(PaymentGateway::class),
            $orderRepo,
            $orderItemRepo,
        );

        $service->finalizePaidOrder($this->makeDto([
            'stripe_session_id' => 'cs_test_123',
            'user_id' => $user->id,
        ]));

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

        app(CheckoutService::class)->finalizePaidOrder($this->makeDto([
            'stripe_session_id' => 'cs_test_456',
            'payment_intent'    => 'pi_test_456',
            'user_id'           => $user->id,
        ]));

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

        app(CheckoutService::class)->finalizePaidOrder($this->makeDto([
            'stripe_session_id' => 'cs_test_789',
            'payment_intent'    => 'pi_test_789',
            'amount_total'      => 500,
            'user_id'           => $user->id,
        ]));
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

        app(CheckoutService::class)->finalizePaidOrder($this->makeDto([
            'stripe_session_id' => 'cs_test_low',
            'payment_intent'    => 'pi_test_low',
            'amount_total'      => 100,
            'user_id'           => $user->id,
        ]));

        Queue::assertPushed(LowStockJob::class);
    }

    public function test_start_checkout_returns_cached_url_without_calling_stripe(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->for(Category::factory())->create([
            'stock_quantity' => 5,
            'price'          => 50,
        ]);

        $cart = $user->cart()->create();
        $cart->items()->create([
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $cachedUrl = 'https://stripe.test/cached';
        Cache::put("checkout_session_user_{$user->id}", $cachedUrl, now()->addMinutes(10));

        /** @var PaymentGateway&\Mockery\MockInterface $gateway */
        $gateway = Mockery::mock(PaymentGateway::class);
        $gateway->shouldNotReceive('createCheckoutSession');
        $this->app->instance(PaymentGateway::class, $gateway);

        $result = app(CheckoutService::class)->startStripeCheckout($user);

        $this->assertEquals($cachedUrl, $result);
    }

    public function test_start_checkout_caches_url_after_stripe_session_created(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->for(Category::factory())->create([
            'stock_quantity' => 5,
            'price'          => 50,
        ]);

        $cart = $user->cart()->create();
        $cart->items()->create([
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        Cache::forget("checkout_session_user_{$user->id}");

        $stripeUrl = 'https://stripe.test/new-session';

        $gateway = Mockery::mock(PaymentGateway::class);
        $gateway->shouldReceive('createCheckoutSession')
            ->once()
            ->andReturn($stripeUrl);
        $this->app->instance(PaymentGateway::class, $gateway);

        $result = app(CheckoutService::class)->startStripeCheckout($user);

        $this->assertEquals($stripeUrl, $result);
        $this->assertEquals($stripeUrl, Cache::get("checkout_session_user_{$user->id}"));
    }
}
