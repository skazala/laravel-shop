<?php

namespace App\Livewire;

use App\Exceptions\InsufficientStockException;
use App\Models\CartItem;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Cart extends Component
{
    public $cart;
    public array $quantities = [];

    public function mount(): void
    {
        if (! Auth::check()) {
            return;
        }

        /** @var User $user */
        $user = Auth::user();

        $this->cart = $user
            ->cart()
            ->with('items.product')
            ->first();

        $this->syncQuantities();
    }

    protected function syncQuantities(): void
    {
        $this->quantities = [];

        if (! $this->cart) {
            return;
        }

        foreach ($this->cart->items as $item) {
            $this->quantities[$item->id] = $item->quantity;
        }
    }

    protected function refreshCart(): void
    {
        if (! $this->cart) {
            return;
        }

        $this->cart->refresh()->load('items.product');
        $this->syncQuantities();
    }

    public function updatedQuantities($value, $key): void
    {
        $itemId = (int) $key;

        if (! isset($this->quantities[$itemId])) {
            return;
        }

        $item = CartItem::with('product')->findOrFail($itemId);

        $max = $item->product->stock_quantity;
        $value = max(1, min((int) $value, $max));

        $this->quantities[$itemId] = $value;
        $item->update(['quantity' => $value]);

        $this->refreshCart();
        $this->dispatch('cart-updated');
    }

    public function removeItem(int $itemId): void
    {
        CartItem::findOrFail($itemId)->delete();

        $this->refreshCart();
        $this->dispatch('cart-updated');
    }

    public function checkout(): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('login');
            return;
        }

        try {
            app(CheckoutService::class)->checkout(Auth::user());

            $this->cart = null;
            $this->quantities = [];

            $this->dispatch('cart-updated');
            session()->flash('success', 'Order placed successfully!');

            $this->redirectRoute('products');
        } catch (InsufficientStockException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Something went wrong during checkout.');
        }
    }

    public function render()
    {
        return view('livewire.cart', [
            'cart' => $this->cart,
        ]);
    }
}
