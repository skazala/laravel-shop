<?php

namespace Tests\Unit\Repositories;

use App\Models\Order;
use App\Models\Product;
use App\Repositories\OrderItemRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderItemRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderItemRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new OrderItemRepository();
    }

    public function test_creates_item_linked_to_order(): void
    {
        /** @var Order $order */
        $order   = Order::factory()->create();
        /** @var Product $product */
        $product = Product::factory()->create();

        $this->repo->createForOrder($order, $product->id, 3, 49.99);

        $this->assertDatabaseHas('order_items', [
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 3,
            'price'      => 49.99,
        ]);
    }

    public function test_creates_multiple_items_for_same_order(): void
    {
        /** @var Order $order */
        $order    = Order::factory()->create();
        /** @var Product $product1 */
        $product1 = Product::factory()->create();
        /** @var Product $product2 */
        $product2 = Product::factory()->create();

        $this->repo->createForOrder($order, $product1->id, 1, 10.00);
        $this->repo->createForOrder($order, $product2->id, 2, 20.00);

        $this->assertCount(2, $order->items);
    }
}
