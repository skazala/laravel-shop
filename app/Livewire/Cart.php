<?php

namespace App\Livewire;

use App\DTO\CartItemDTO;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Cart extends Component
{
    /** @var array<int, array<string, mixed>> */
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
            fn (CartItemDTO $item) => $item->toArray(),
            $items
        );

        $this->quantities = [];
        foreach ($this->items as $item) {
            $this->quantities[$item['key']] = $item['quantity'];
        }
    }

    public function updatedQuantities(mixed $value, string $key): void
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

    public function render(): View
    {
        return view('livewire.cart');
    }

    public function checkout(): RedirectResponse
    {
        if (! Auth::check()) {
            session(['url.intended' => route('cart')]);

            return redirect()->guest(route('login'));
        }

        $url = app(CheckoutService::class)->startStripeCheckout(Auth::user());

        if (! $url) {
            session()->flash('error', 'Your cart is empty.');

            return redirect()->route('cart');
        }

        return redirect()->away($url);
    }
}
