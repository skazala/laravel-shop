<x-mail::message>
    # Order Confirmed

    Hi {{ $order->user->name }}, thank you for your order!

    **Order #{{ $order->id }}** &mdash; {{ $order->created_at->format('M j, Y') }}

    @foreach ($order->items as $item)
        - {{ $item->product->name }} x{{ $item->quantity }} — ${{ number_format($item->price * $item->quantity, 2) }}
    @endforeach

    **Total: ${{ number_format($order->total_price, 2) }}**

    Thanks for shopping with us!

    {{ config('app.name') }}
</x-mail::message>
