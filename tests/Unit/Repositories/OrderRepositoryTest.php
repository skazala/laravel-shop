<?php

namespace Tests\Unit\Repositories;

use App\Models\Order;
use App\Models\User;
use App\OrderStatus;
use App\Repositories\OrderRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new OrderRepository();
    }

    public function test_all_for_user_returns_only_that_users_orders(): void
    {
        $user  = User::factory()->create();
        $other = User::factory()->create();

        Order::factory()->count(2)->create(['user_id' => $user->id]);
        Order::factory()->count(3)->create(['user_id' => $other->id]);

        $result = $this->repo->allForUser($user);

        $this->assertCount(2, $result);
        $result->each(fn ($o) => $this->assertEquals($user->id, $o->user_id));
    }

    public function test_all_for_user_returns_orders_latest_first(): void
    {
        $user = User::factory()->create();

        $first  = Order::factory()->create(['user_id' => $user->id, 'created_at' => now()->subDay()]);
        $second = Order::factory()->create(['user_id' => $user->id, 'created_at' => now()]);

        $result = $this->repo->allForUser($user);

        $this->assertEquals($second->id, $result->first()->id);
    }

    public function test_exists_by_stripe_session_id_returns_true_when_found(): void
    {
        Order::factory()->create(['stripe_session_id' => 'cs_test_123']);

        $this->assertTrue($this->repo->existsByStripeSessionId('cs_test_123'));
    }

    public function test_exists_by_stripe_session_id_returns_false_when_not_found(): void
    {
        $this->assertFalse($this->repo->existsByStripeSessionId('cs_nonexistent'));
    }

    public function test_create_persists_order_with_correct_data(): void
    {
        $user = User::factory()->create();

        $order = $this->repo->create([
            'user_id'                  => $user->id,
            'total_price'              => 199.99,
            'status'                   => OrderStatus::Paid,
            'stripe_session_id'        => 'cs_test_999',
            'stripe_payment_intent_id' => 'pi_test_999',
            'stripe_amount_total'      => 19999,
            'currency'                 => 'usd',
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertDatabaseHas('orders', [
            'stripe_session_id' => 'cs_test_999',
            'total_price'       => 199.99,
        ]);
    }
}
