<div>
    @if (session()->has('success'))
        <div class="mb-4 rounded bg-green-100 px-4 py-2 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <h1 class="text-xl font-bold mb-4">Products</h1>

    @foreach ($products as $product)
        <div class="mb-6">
            <strong>{{ $product['name'] }}</strong><br>

            Price: {{ $product['price'] }}$<br>

            Available: {{ $product['available_quantity'] }}<br>

            @if ($product['in_cart'] > 0)
                <span class="text-sm text-gray-600">
                    In cart: {{ $product['in_cart'] }}
                </span><br>
            @endif

            <button wire:click="addToCart({{ $product['id'] }})"
                class="mt-2 px-3 py-1 border
           disabled:opacity-50
           disabled:cursor-not-allowed
           disabled:bg-gray-100
           disabled:text-gray-400"
                @disabled($product['available_quantity'] === 0)>
                Add to cart
            </button>

            <hr class="mt-4">
        </div>
    @endforeach
</div>
