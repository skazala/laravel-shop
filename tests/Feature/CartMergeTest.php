<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartMergeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cart_is_merged_into_user_cart_on_login()
    {
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();

        $this->withSession([
            'cart' => [
                $productA->id => 2,
                $productB->id => 1,
            ],
        ]);

        $user = User::factory()->create();

        $cart = Cart::factory()->create([
            'user_id' => $user->id,
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $productA->id,
            'quantity' => 1,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $productA->id,
            'quantity' => 3,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $productB->id,
            'quantity' => 1,
        ]);

        $this->assertEmpty(session('cart'));
    }

    public function test_guest_cart_is_merged_on_registration()
    {
        $product = Product::factory()->create();

        $this->withSession([
            'cart' => [
                $product->id => 2,
            ],
        ]);

        $this->post('/register', [
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::whereEmail('test@example.com')->firstOrFail();
        $cart = $user->cart()->first();

        $this->assertNotNull($cart, 'Cart was not created for user');

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->assertEmpty(session('cart'));
    }
}
