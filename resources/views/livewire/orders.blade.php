@php use App\OrderStatus; @endphp

<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-2xl font-semibold mb-6">My Orders</h1>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="mb-6 p-4 bg-yellow-100 text-yellow-800 rounded-lg">{{ session('warning') }}</div>
    @endif

    @forelse ($orders as $order)
        <div class="border rounded-lg mb-6 overflow-hidden">

            {{-- Order header --}}
            <div class="flex items-center justify-between bg-gray-50 px-5 py-3">
                <div class="text-sm text-gray-600">
                    <span class="font-medium text-gray-900">Order #{{ $order->id }}</span>
                    &mdash; {{ $order->createdAt }}
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium">${{ number_format($order->totalPrice, 2) }}</span>
                    <span @class([
                        'px-2 py-0.5 rounded-full text-xs font-semibold',
                        'bg-yellow-100 text-yellow-700' => $order->status === OrderStatus::Pending,
                        'bg-green-100  text-green-700' => $order->status === OrderStatus::Paid,
                        'bg-blue-100   text-blue-700' => $order->status === OrderStatus::Shipped,
                        'bg-purple-100 text-purple-700' =>
                            $order->status === OrderStatus::Delivered,
                        'bg-red-100    text-red-700' => in_array($order->status, [
                            OrderStatus::Cancelled,
                            OrderStatus::Failed,
                        ]),
                    ])>
                        {{ ucfirst($order->status->value) }}
                    </span>
                </div>
            </div>

            {{-- Status timeline --}}
            @php
                $steps = [OrderStatus::Paid, OrderStatus::Shipped, OrderStatus::Delivered];
                $currentIndex = collect($steps)->search($order->status) ?? -1;
            @endphp

            @if (!in_array($order->status, [OrderStatus::Cancelled, OrderStatus::Failed, OrderStatus::Pending]))
                <div class="px-5 py-4 bg-white border-b">
                    <ol class="flex items-center gap-0">
                        @foreach ($steps as $i => $step)
                            @php $reached = $i <= $currentIndex; @endphp
                            <li class="flex items-center {{ $i < count($steps) - 1 ? 'flex-1' : '' }}">
                                {{-- Circle --}}
                                <div @class([
                                    'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-colors',
                                    'bg-blue-500 border-blue-500 text-white' => $reached,
                                    'bg-white border-gray-300 text-gray-400' => !$reached,
                                ])>
                                    @if ($reached)
                                        ✓
                                    @else
                                        {{ $i + 1 }}
                                    @endif
                                </div>
                                {{-- Label --}}
                                <span @class([
                                    'ml-2 text-xs font-medium',
                                    'text-blue-600' => $reached,
                                    'text-gray-400' => !$reached,
                                ])>
                                    {{ ucfirst($step->value) }}
                                </span>
                                {{-- Connector line --}}
                                @if ($i < count($steps) - 1)
                                    <div @class([
                                        'flex-1 h-0.5 mx-3',
                                        'bg-blue-400' => $i < $currentIndex,
                                        'bg-gray-200' => $i >= $currentIndex,
                                    ])></div>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif

            {{-- Order items --}}
            <ul class="divide-y">
                @foreach ($order->items as $item)
                    <li class="flex items-center justify-between px-5 py-3 text-sm">
                        <span class="text-gray-800">
                            {{ $item->productName }}
                            <span class="text-gray-400">&times; {{ $item->quantity }}</span>
                        </span>
                        <span class="text-gray-700">${{ number_format($item->subtotal(), 2) }}</span>
                    </li>
                @endforeach
            </ul>

            {{-- Admin controls --}}
            @if (Auth::user()->is_admin)
                <div class="flex gap-3 px-5 py-3 bg-gray-50 border-t">
                    <span class="text-xs text-gray-500 self-center mr-auto">Admin</span>
                    @if ($order->status === OrderStatus::Paid)
                        <button wire:click="markShipped({{ $order->id }})"
                            class="px-3 py-1.5 text-xs font-medium rounded-md bg-blue-500 text-white hover:bg-blue-600 transition">
                            Mark as Shipped
                        </button>
                    @endif
                    @if ($order->status === OrderStatus::Shipped)
                        <button wire:click="markDelivered({{ $order->id }})"
                            class="px-3 py-1.5 text-xs font-medium rounded-md bg-purple-500 text-white hover:bg-purple-600 transition">
                            Mark as Delivered
                        </button>
                    @endif
                </div>
            @endif

        </div>
    @empty
        <p class="text-gray-500">You haven't placed any orders yet.</p>
    @endforelse
</div>
