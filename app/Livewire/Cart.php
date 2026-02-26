<?php

namespace App\Livewire;

use App\DTO\CartItemDTO;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Cart extends Component
{
    /** @var CartItemDTO[] */
    public array $items = [];

    /** @var array<string,int> */
    public array $quantities = [];

    public function mount(): void
    {
        $this->loadCart();
    }

    protected function cartService(): CartService
    {
        return app(CartService::class);
    }

    protected function loadCart(): void
    {
        $items = $this->cartService()->getItems();

        $this->items = array_map(
            fn(CartItemDTO $item) => $item->toArray(),
            $items
        );

        $this->quantities = [];
        foreach ($this->items as $item) {
            $this->quantities[$item['key']] = $item['quantity'];
        }
    }

    public function updatedQuantities($value, string $key): void
    {
        $this->cartService()->updateQuantity($key, (int) $value);

        $this->loadCart();
        $this->dispatch('cart-updated');
    }

    public function removeItem(string $key): void
    {
        $this->cartService()->remove($key);

        $this->loadCart();
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.cart');
    }

    public function checkout()
    {
        if (!Auth::check()) {
            session(['url.intended' => route('cart')]);

            return redirect()->guest(route('login'));
        }

        $url = app(CheckoutService::class)->startStripeCheckout(Auth::user());

        if (!$url) {
            session()->flash('error', 'Your cart is empty.');

            return redirect()->route('cart');
        }

        return redirect()->away($url);
    }
}
