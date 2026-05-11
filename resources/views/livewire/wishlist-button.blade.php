<div>
    @auth
        <button wire:click="toggle" title="{{ $wishlisted ? 'Remove from wishlist' : 'Add to wishlist' }}"
            class="transition-colors focus:outline-none">
            @if ($wishlisted)
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5
                             2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09
                             C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5
                             c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                </svg>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400 hover:text-red-400" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318
                             a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682
                             a4.5 4.5 0 010-6.364z" />
                </svg>
            @endif
        </button>
    @endauth
</div>
