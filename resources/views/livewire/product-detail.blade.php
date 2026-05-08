<div class="max-w-3xl mx-auto py-8 px-4">

    {{-- Back link --}}
    <a href="{{ route('products') }}" class="text-sm text-blue-600 hover:underline">&larr; Back to products</a>

    {{-- Product info --}}
    <div class="mt-6 border rounded-lg p-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h1>

        {{-- Average rating --}}
        @php $avg = $product->averageRating(); @endphp
        <div class="mt-1 flex items-center gap-2">
            @if ($avg)
                <span class="text-yellow-400 text-lg">
                    @for ($i = 1; $i <= 5; $i++)
                        {{ $i <= round($avg) ? '★' : '☆' }}
                    @endfor
                </span>
                <span class="text-sm text-gray-600">{{ $avg }} / 5 ({{ $product->reviews->count() }}
                    reviews)</span>
            @else
                <span class="text-sm text-gray-400">No reviews yet</span>
            @endif
        </div>

        <div class="mt-4 text-xl font-semibold text-gray-800">
            ${{ number_format($product->price, 2) }}
        </div>
        <div class="mt-1 text-sm text-gray-500">
            {{ $product->stock_quantity > 0 ? $product->stock_quantity . ' in stock' : 'Out of stock' }}
        </div>

        <button wire:click="addToCart"
            class="mt-4 px-4 py-2 border rounded disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400"
            @disabled($product->stock_quantity === 0)>
            Add to cart
        </button>

        @if (session('success'))
            <p class="mt-2 text-sm text-green-600">{{ session('success') }}</p>
        @endif
    </div>

    {{-- Review form --}}
    <div class="mt-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer Reviews</h2>

        @if ($reviewSuccess)
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm">
                {{ $reviewSuccess }}
            </div>
        @endif

        @if ($canReview)
            <div class="border rounded-lg p-5 mb-6 bg-gray-50">
                <h3 class="font-medium text-gray-800 mb-3">Leave a Review</h3>

                {{-- Star picker --}}
                <div class="flex gap-1 mb-3" x-data>
                    @for ($i = 1; $i <= 5; $i++)
                        <button type="button" wire:click="$set('rating', {{ $i }})"
                            class="text-2xl transition-colors {{ $i <= $rating ? 'text-yellow-400' : 'text-gray-300' }} hover:text-yellow-400">★</button>
                    @endfor
                    @error('rating')
                        <span class="ml-2 text-xs text-red-600 self-center">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Comment --}}
                <textarea wire:model="comment" rows="4"
                    placeholder="Share your experience with this product... (min. 10 characters)"
                    class="w-full border rounded-md p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 resize-none"></textarea>
                @error('comment')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror

                <button wire:click="submitReview"
                    class="mt-3 px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 transition">
                    Submit Review
                </button>
            </div>
        @elseif (!Auth::check())
            <p class="text-sm text-gray-500 mb-6">
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Log in</a> to leave a review.
            </p>
        @elseif (!$hasReviewed)
            <p class="text-sm text-gray-500 mb-6">
                Only customers who have purchased this product can leave a review.
            </p>
        @elseif ($hasReviewed)
            <p class="text-sm text-gray-500 mb-6">You have already reviewed this product. Thank you!</p>
        @endif

        {{-- Reviews list --}}
        @forelse ($product->reviews as $review)
            <div class="border-b py-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-800">{{ $review->user->name }}</span>
                    <span class="text-xs text-gray-400">{{ $review->created_at->format('M j, Y') }}</span>
                </div>
                <div class="text-yellow-400 mt-1">
                    @for ($i = 1; $i <= 5; $i++)
                        {{ $i <= $review->rating ? '★' : '☆' }}
                    @endfor
                </div>
                <p class="mt-1 text-sm text-gray-700">{{ $review->comment }}</p>
            </div>
        @empty
            <p class="text-sm text-gray-400">No reviews yet. Be the first!</p>
        @endforelse
    </div>

</div>
