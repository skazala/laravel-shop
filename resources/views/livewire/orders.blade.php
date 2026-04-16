<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-2xl font-semibold mb-6">My Orders</h1>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="mb-6 p-4 bg-yellow-100 text-yellow-800 rounded-lg">
            {{ session('warning') }}
        </div>
    @endif

    @forelse ($orders as $order)
        <div class="border rounded-lg mb-6 overflow-hidden">
            {{-- Order header --}}
            <div class="flex items-center justify-between bg-gray-50 px-5 py-3">
                <div class="text-sm text-gray-600">
                    <span class="font-medium text-gray-900">Order #{{ $order->id }}</span>
                    &mdash; {{ $order->created_at->format('M j, Y') }}
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium">
                        ${{ number_format($order->total_price, 2) }}
                    </span>
                    <span
                        class="px-2 py-0.5 rounded-full text-xs font-semibold
                        {{ $order->status->value === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ ucfirst($order->status->value) }}
                    </span>
                </div>
            </div>

            {{-- Order items --}}
            <ul class="divide-y">
                @foreach ($order->items as $item)
                    <li class="flex items-center justify-between px-5 py-3 text-sm">
                        <span class="text-gray-800">
                            {{ $item->product->name }}
                            <span class="text-gray-400">&times; {{ $item->quantity }}</span>
                        </span>
                        <span class="text-gray-700">
                            ${{ number_format($item->price * $item->quantity, 2) }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @empty
        <p class="text-gray-500">You haven't placed any orders yet.</p>
    @endforelse
</div>
