<header class="p-4 border-b">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('products') }}"
                class="flex items-center gap-3 font-bold text-lg hover:opacity-90 transition">
                <img src="{{ asset('images/little-yarn-shop-logo.png') }}" alt="Little Yarn Shop logo"
                    class="h-14 w-14 md:h-20 md:w-20" />
                <span>Little Yarn Shop</span>
            </a>
            <livewire:cart-dropdown />
            @auth
                | <a href="{{ route('orders') }}" class="text-sm text-gray-700 hover:text-gray-900">
                    My Orders
                </a>
                | <a href="{{ route('wishlist') }}" class="text-sm text-gray-700 hover:text-gray-900">
                    My Wishlist
                </a>
            @endauth
        </div>
        <div class="flex gap-2 items-center">
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="px-3 py-1 border rounded">
                        Logout
                    </button>
                </form>
            @else
                @include('partials.login-modal')
                <a href="{{ route('register') }}" class="px-3 py-1 border rounded">
                    Register
                </a>
            @endauth
        </div>
    </div>

    @if (request()->routeIs('products'))
        <div class="mt-4 flex gap-3 text-sm flex-wrap">
            @php
                $activeCategory = request()->query('category', null);
            @endphp
            <a href="{{ route('products') }}" @if (empty($activeCategory)) aria-current="page" @endif
                class="px-3 py-1 border rounded transition
                {{ empty($activeCategory) ? 'bg-blue-200 text-blue-800 border-blue-300' : 'hover:bg-gray-100' }}">
                All
            </a>
            @foreach ($categories as $cat)
                <a href="{{ route('products', array_merge(request()->query(), ['category' => $cat->slug])) }}"
                    @if ($activeCategory === $cat->slug) aria-current="page" @endif
                    class="px-3 py-1 border rounded transition
                    {{ $activeCategory === $cat->slug ? 'bg-green-100 border-green-600' : 'hover:bg-gray-100' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    @endif
</header>
