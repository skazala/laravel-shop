<header class="p-4 border-b flex items-center justify-between">
    <div class="flex items-center gap-4">
        <a href="{{ route('products') }}" class="flex items-center gap-3 font-bold text-lg hover:opacity-90 transition">
            <img src="{{ asset('images/little-yarn-shop-logo.png') }}" alt="Little Yarn Shop logo"
                class="h-14 w-14 md:h-20 md:w-20" />

            <span>Little Yarn Shop</span>
        </a>

        <livewire:cart-badge />
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
</header>
