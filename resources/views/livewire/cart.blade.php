<div>
    <h1 class="text-xl font-bold mb-4">Your cart</h1>

    @if ($cart && $cart->items->count())
        <ul>
            @foreach ($cart->items as $item)
                <li wire:key="cart-item-{{ $item->id }}" class="mb-4 flex items-center gap-4" x-data="{
                    quantity: @entangle('quantities.' . $item->id).live,
                    price: {{ $item->product->price }},
                }">
                    <span class="w-40">
                        {{ $item->product->name }}
                    </span>

                    <input type="number" min="1" class="w-20 border px-2 py-1" x-model.number="quantity" />

                    <span class="w-24">
                        <span x-text="(price * (quantity ?? 0)).toFixed(2)"></span>$
                    </span>

                    <button wire:click="removeItem({{ $item->id }})" class="text-red-600 text-sm">
                        ✕
                    </button>
                </li>
            @endforeach
        </ul>

        <button wire:click="checkout" class="mt-4 px-4 py-2 border">
            Checkout
        </button>
    @else
        <p>Your cart is empty.</p>
    @endif
</div>
