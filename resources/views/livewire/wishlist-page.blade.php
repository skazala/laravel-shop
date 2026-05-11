<div class="max-w-3xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-semibold mb-6">My Wishlist</h1>

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if ($products->isEmpty())
        <p class="text-gray-500">Your wishlist is empty.</p>
        <button onclick="window.location='{{ route('products') }}'"
            class="mt-4 px-4 py-2 border rounded text-sm text-gray-700 hover:bg-gray-50 transition cursor-pointer">
            Browse Products
        </button>
    @else
        <ul class="divide-y border rounded-lg overflow-hidden">
            @foreach ($products as $product)
                <li class="flex items-center justify-between px-5 py-4 bg-white">
                    <div>
                        <button onclick="window.location='{{ route('products.show', $product->id) }}'"
                            class="font-medium text-gray-900 hover:underline cursor-pointer">
                            {{ $product->name }}
                        </button>
                        <p class="text-sm text-gray-500 mt-0.5">
                            ${{ number_format($product->price, 2) }}
                            &bull;
                            {{ $product->stock_quantity > 0 ? $product->stock_quantity . ' in stock' : 'Out of stock' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="addToCart({{ $product->id }})"
                            class="px-3 py-1.5 border rounded text-sm text-gray-700 hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed"
                            @disabled($product->stock_quantity === 0)>
                            Add to cart
                        </button>
                        <button wire:click="remove({{ $product->id }})"
                            class="text-gray-400 hover:text-red-500 transition" title="Remove from wishlist">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5
                                         2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09
                                         C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5
                                         c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                            </svg>
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
