<div>
    <h1 class="text-xl font-bold mb-4">Your cart</h1>

    @if (!empty($items))
        <ul>
            @foreach ($items as $item)
                <li
                    wire:key="cart-item-{{ $item['key'] }}"
                    class="mb-4 flex items-center gap-4"
                    x-data="{
                        quantity: @entangle('quantities.' . $item['key']).live,
                        price: {{ $item['price'] }},
                    }"
                >
                    <span class="w-40">
                        {{ $item['name'] }}
                    </span>

                    <input
                        type="number"
                        min="1"
                        max="{{ $item['max'] }}"
                        class="w-20 border px-2 py-1"
                        x-model.number="quantity"
                    />

                    <span class="w-24">
                        <span x-text="(price * (quantity ?? 0)).toFixed(2)"></span>$
                    </span>

                    <button
                        wire:click="removeItem('{{ $item['key'] }}')"
                        class="text-red-600 text-sm"
                    >
                        ✕
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="mt-4 flex items-center gap-4">
            <button wire:click="checkout" class="px-4 py-2 border">
                Checkout
            </button>
        </div>
    @else
        <p>Your cart is empty.</p>
    @endif
</div>
