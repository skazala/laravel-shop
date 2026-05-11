<div class="relative inline-flex items-center" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
    {{-- Cart icon --}}
    <a href="{{ route('cart') }}" class="relative inline-flex items-center">
        🛒
        @if ($this->count > 0)
            <span class="absolute -top-2 -right-2 rounded-full bg-red-600 px-2 text-xs text-white">
                {{ $this->count }}
            </span>
        @endif
    </a>

    {{-- Dropdown popup --}}
    <div x-show="open" style="display:none"
        class="absolute top-full left-0 z-50 w-80 rounded-lg border border-gray-200 bg-white shadow-xl
               transition-opacity duration-150"
        :class="open ? 'opacity-100' : 'opacity-0'">

        <div class="h-2 w-full"></div>

        @if (empty($this->items))
            <p class="p-4 text-center text-sm text-gray-500">Your cart is empty.</p>
        @else
            <ul class="max-h-72 divide-y divide-gray-100 overflow-y-auto">
                @foreach ($this->items as $item)
                    <li class="flex items-center gap-3 px-4 py-3">
                        @if (!empty($item['image']))
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                                class="h-10 w-10 rounded object-cover">
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="truncate text-sm font-medium text-gray-800">
                                {{ $item['name'] }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $item['quantity'] }} &times; {{ number_format($item['price'], 2) }} &euro;
                            </p>
                        </div>
                        <span class="text-sm font-semibold text-gray-700">
                            {{ number_format($item['quantity'] * $item['price'], 2) }} &euro;
                        </span>
                    </li>
                @endforeach
            </ul>

            <div class="flex justify-between border-t border-gray-200 px-4 py-2 text-sm font-semibold text-gray-800">
                <span>Total</span>
                <span>
                    {{ number_format(array_sum(array_map(fn($i) => $i['quantity'] * $i['price'], $this->items)), 2) }}
                    &euro;
                </span>
            </div>

            <div class="flex gap-2 border-t border-gray-200 p-3">
                <button onclick="window.location='{{ route('cart') }}'"
                    class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50 transition cursor-pointer">
                    Go to Cart
                </button>
                <form method="POST" action="{{ route('checkout') }}" class="flex-1">
                    @csrf
                    <button type="submit"
                        class="w-full rounded-md bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700 transition">
                        Checkout
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
