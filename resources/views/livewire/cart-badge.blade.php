<a href="{{ route('cart') }}" class="relative inline-flex items-center">
    🛒
    @if ($this->count > 0)
        <span class="absolute -top-2 -right-2 rounded-full bg-red-600 px-2 text-xs text-red">
            {{ $this->count }}
        </span>
    @endif
</a>
