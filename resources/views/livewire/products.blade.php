<div>
    @if (session()->has('success'))
        <div class="mb-4 rounded bg-green-100 px-4 py-2 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <h1 class="text-xl font-bold mb-4">Products</h1>

    @forelse ($products as $product)
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <strong>
                    <a href="{{ route('products.show', $product->id) }}" class="hover:underline text-gray-900">
                        {{ $product->name }}
                    </a>
                </strong>
                @php $avg = $product->averageRating(); @endphp
                @if ($avg)
                    <span class="text-yellow-400 text-sm">
                        @for ($i = 1; $i <= 5; $i++)
                            {{ $i <= round($avg) ? '★' : '☆' }}
                        @endfor
                        <span class="text-gray-500 text-xs ml-1">{{ $avg }}</span>
                    </span>
                @endif
            </div>

            Price: {{ number_format($product->price, 2) }}$<br>
            Available: {{ $product->available_quantity }}<br>

            @if ($product->in_cart > 0)
                <span class="text-sm text-gray-600">In cart: {{ $product->in_cart }}</span><br>
            @endif

            <div class="mt-2 flex items-center gap-2">
                <button wire:click="addToCart({{ $product->id }})"
                    class="px-3 py-1 border disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400"
                    @disabled($product->available_quantity === 0)>
                    Add to cart
                </button>
                <button onclick="window.location='{{ route('products.show', $product->id) }}'"
                    class="px-3 py-1 border rounded text-sm text-gray-700 hover:bg-gray-50 transition cursor-pointer">
                    View
                </button>
                <livewire:wishlist-button :productId="$product->id" :key="'wish-' . $product->id" />
            </div>
            <hr class="mt-4">
        </div>
    @empty
        <p>No products found.</p>
    @endforelse

    <div class="mt-10">
        {{ $products->links() }}
    </div>
</div>
