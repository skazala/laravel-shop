<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Component;

class Cart extends Component
{
    public array $items = [];
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
        $this->items = $this->cartService()->getItems();

        $this->quantities = [];
        foreach ($this->items as $item) {
            $this->quantities[$item['key']] = $item['quantity'];
        }
    }

    public function updatedQuantities($value, $key): void
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
}
