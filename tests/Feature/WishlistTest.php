<?php

namespace Tests\Feature;

use App\Livewire\WishlistButton;
use App\Livewire\WishlistPage;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(): Product
    {
        return Product::factory()
            ->for(Category::factory())
            ->create();
    }

    public function test_authenticated_user_can_add_product_to_wishlist(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        Livewire::actingAs($user)
            ->test(WishlistButton::class, ['productId' => $product->id])
            ->call('toggle');


        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_toggle_sets_wishlisted_true_when_adding(): void
    {
        $user    = User::factory()->create();
        $product = $this->makeProduct();

        Livewire::actingAs($user)
            ->test(WishlistButton::class, ['productId' => $product->id])
            ->call('toggle')
            ->assertSet('wishlisted', true);
    }

    public function test_authenticated_user_can_remove_product_from_wishlist(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        Livewire::actingAs($user)
            ->test(WishlistButton::class, ['productId' => $product->id])
            ->call('toggle');

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_toggle_sets_wishlisted_false_when_removing(): void
    {
        $user    = User::factory()->create();
        $product = $this->makeProduct();

        Wishlist::create(['user_id' => $user->id, 'product_id' => $product->id]);

        Livewire::actingAs($user)
            ->test(WishlistButton::class, ['productId' => $product->id])
            ->call('toggle')
            ->assertSet('wishlisted', false);
    }

    public function test_toggling_twice_leaves_no_wishlist_entry(): void
    {
        $user    = User::factory()->create();
        $product = $this->makeProduct();

        $component = Livewire::actingAs($user)
            ->test(WishlistButton::class, ['productId' => $product->id]);

        $component->call('toggle');
        $component->call('toggle');

        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_toggling_three_times_leaves_one_wishlist_entry(): void
    {
        $user    = User::factory()->create();
        $product = $this->makeProduct();

        $component = Livewire::actingAs($user)
            ->test(WishlistButton::class, ['productId' => $product->id]);

        $component->call('toggle');
        $component->call('toggle');
        $component->call('toggle');

        $this->assertDatabaseCount('wishlists', 1);
    }

    public function test_wishlisted_is_true_on_mount_when_already_in_wishlist(): void
    {
        $user    = User::factory()->create();
        $product = $this->makeProduct();

        Wishlist::create(['user_id' => $user->id, 'product_id' => $product->id]);

        Livewire::actingAs($user)
            ->test(WishlistButton::class, ['productId' => $product->id])
            ->assertSet('wishlisted', true);
    }

    public function test_wishlisted_is_false_on_mount_when_not_in_wishlist(): void
    {
        $user    = User::factory()->create();
        $product = $this->makeProduct();

        Livewire::actingAs($user)
            ->test(WishlistButton::class, ['productId' => $product->id])
            ->assertSet('wishlisted', false);
    }

    public function test_authenticated_user_can_view_wishlist_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('wishlist'))
            ->assertOk();
    }

    public function test_wishlist_page_requires_authentication(): void
    {
        $this->get(route('wishlist'))
            ->assertRedirect(route('login'));
    }

    public function test_wishlist_page_shows_wishlisted_products(): void
    {
        $user     = User::factory()->create();
        $product  = $this->makeProduct();

        Wishlist::create(['user_id' => $user->id, 'product_id' => $product->id]);

        Livewire::actingAs($user)
            ->test(WishlistPage::class)
            ->assertSee($product->name);
    }

    public function test_wishlist_page_can_remove_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        Livewire::actingAs($user)
            ->test(WishlistPage::class)
            ->call('remove', $product->id);

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_wishlist_page_is_empty_when_nothing_wishlisted(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(WishlistPage::class)
            ->assertSee('Your wishlist is empty.');
    }

    public function test_wishlist_page_does_not_show_other_users_products(): void
    {
        $userA   = User::factory()->create();
        $userB   = User::factory()->create();
        $product = $this->makeProduct();

        Wishlist::create(['user_id' => $userB->id, 'product_id' => $product->id]);

        Livewire::actingAs($userA)
            ->test(WishlistPage::class)
            ->assertDontSee($product->name);
    }
}
