<?php

namespace Tests\Feature;

use App\Livewire\ProductDetail;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeProduct(): Product
    {
        return Product::factory()
            ->for(Category::factory())
            ->create();
    }

    private function makeUserWhoPurchased(Product $product): User
    {
        $user  = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status'  => OrderStatus::Paid,
        ]);
        OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 1,
            'price'      => $product->price,
        ]);

        return $user;
    }

    // -------------------------------------------------------------------------
    // Purchase gate — who CAN review
    // -------------------------------------------------------------------------

    public function test_buyer_can_submit_review(): void
    {
        $product = $this->makeProduct();
        $user    = $this->makeUserWhoPurchased($product);

        Livewire::actingAs($user)
            ->test(ProductDetail::class, ['id' => $product->id])
            ->set('rating', 5)
            ->set('comment', 'Absolutely love this yarn, very soft!')
            ->call('submitReview')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('reviews', [
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'rating'     => 5,
        ]);
    }

    public function test_buyer_with_shipped_order_can_review(): void
    {
        $product = $this->makeProduct();
        $user    = User::factory()->create();
        $order   = Order::factory()->create([
            'user_id' => $user->id,
            'status'  => OrderStatus::Shipped,
        ]);
        OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 1,
            'price'      => $product->price,
        ]);

        Livewire::actingAs($user)
            ->test(ProductDetail::class, ['id' => $product->id])
            ->set('rating', 4)
            ->set('comment', 'Great product, arrived quickly!')
            ->call('submitReview')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('reviews', [
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'rating'     => 4,
        ]);
    }

    public function test_buyer_with_delivered_order_can_review(): void
    {
        $product = $this->makeProduct();
        $user    = User::factory()->create();
        $order   = Order::factory()->create([
            'user_id' => $user->id,
            'status'  => OrderStatus::Delivered,
        ]);
        OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 1,
            'price'      => $product->price,
        ]);

        Livewire::actingAs($user)
            ->test(ProductDetail::class, ['id' => $product->id])
            ->set('rating', 3)
            ->set('comment', 'Decent quality but took a while to arrive.')
            ->call('submitReview')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Purchase gate — who CANNOT review
    // -------------------------------------------------------------------------

    public function test_guest_cannot_submit_review(): void
    {
        $product = $this->makeProduct();

        Livewire::test(ProductDetail::class, ['id' => $product->id])
            ->set('rating', 5)
            ->set('comment', 'Great product!')
            ->call('submitReview')
            ->assertUnauthorized();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_authenticated_non_buyer_cannot_submit_review(): void
    {
        $product  = $this->makeProduct();
        $nonBuyer = User::factory()->create();

        Livewire::actingAs($nonBuyer)
            ->test(ProductDetail::class, ['id' => $product->id])
            ->set('rating', 5)
            ->set('comment', 'I want to review this product!')
            ->call('submitReview')
            ->assertForbidden();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_cancelled_order_does_not_grant_review_rights(): void
    {
        $product = $this->makeProduct();
        $user    = User::factory()->create();
        $order   = Order::factory()->create([
            'user_id' => $user->id,
            'status'  => OrderStatus::Cancelled,
        ]);
        OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
        ]);

        Livewire::actingAs($user)
            ->test(ProductDetail::class, ['id' => $product->id])
            ->set('rating', 5)
            ->set('comment', 'Should not be allowed to post this.')
            ->call('submitReview')
            ->assertForbidden();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_failed_order_does_not_grant_review_rights(): void
    {
        $product = $this->makeProduct();
        $user    = User::factory()->create();
        $order   = Order::factory()->create([
            'user_id' => $user->id,
            'status'  => OrderStatus::Failed,
        ]);
        OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
        ]);

        Livewire::actingAs($user)
            ->test(ProductDetail::class, ['id' => $product->id])
            ->set('rating', 5)
            ->set('comment', 'Should not be allowed to post this.')
            ->call('submitReview')
            ->assertForbidden();

        $this->assertDatabaseCount('reviews', 0);
    }

    // -------------------------------------------------------------------------
    // Double review prevention
    // -------------------------------------------------------------------------

    public function test_buyer_cannot_review_the_same_product_twice(): void
    {
        $product = $this->makeProduct();
        $user    = $this->makeUserWhoPurchased($product);

        // First review — should succeed
        Review::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'rating'     => 4,
            'comment'    => 'First review, already submitted.',
        ]);

        // Second attempt — should be blocked
        Livewire::actingAs($user)
            ->test(ProductDetail::class, ['id' => $product->id])
            ->set('rating', 5)
            ->set('comment', 'Trying to review again!')
            ->call('submitReview')
            ->assertForbidden();

        $this->assertDatabaseCount('reviews', 1);
    }

    // -------------------------------------------------------------------------
    // UI state reflects authorization correctly
    // -------------------------------------------------------------------------

    public function test_canReview_is_true_for_eligible_buyer(): void
    {
        $product = $this->makeProduct();
        $user    = $this->makeUserWhoPurchased($product);

        Livewire::actingAs($user)
            ->test(ProductDetail::class, ['id' => $product->id])
            ->assertSet('canReview', true)
            ->assertSet('hasReviewed', false);
    }

    public function test_canReview_is_false_for_non_buyer(): void
    {
        $product  = $this->makeProduct();
        $nonBuyer = User::factory()->create();

        Livewire::actingAs($nonBuyer)
            ->test(ProductDetail::class, ['id' => $product->id])
            ->assertSet('canReview', false);
    }

    public function test_canReview_is_false_after_reviewing(): void
    {
        $product = $this->makeProduct();
        $user    = $this->makeUserWhoPurchased($product);

        Review::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'rating'     => 5,
            'comment'    => 'Already reviewed.',
        ]);

        Livewire::actingAs($user)
            ->test(ProductDetail::class, ['id' => $product->id])
            ->assertSet('canReview', false)
            ->assertSet('hasReviewed', true);
    }

    public function test_hasReviewed_is_false_for_guest(): void
    {
        $product = $this->makeProduct();

        Livewire::test(ProductDetail::class, ['id' => $product->id])
            ->assertSet('canReview', false)
            ->assertSet('hasReviewed', false);
    }
}
