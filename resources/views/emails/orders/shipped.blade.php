@component('mail::message')
    # Your order is on its way! 📦

    Hi {{ $order->user->name }},

    Your **Order #{{ $order->id }}** has been shipped and is on its way to you.

    @foreach ($order->items as $item)
        - {{ $item->product->name }} x{{ $item->quantity }} — ${{ number_format($item->quantity * $item->unit_price, 2) }} |
    @endforeach

    **Total: ${{ number_format($order->total_price, 2) }}**

    @component('mail::button', ['url' => route('orders')])
        View My Orders
    @endcomponent

    Thanks for shopping with us!
    {{ config('app.name') }}
@endcomponent
