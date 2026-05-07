@component('mail::message')
    # Your order has been delivered! 🎉

    Hi {{ $order->user->name }},

    **Order #{{ $order->id }}** has been marked as delivered. We hope you love your purchase!

    @component('mail::button', ['url' => route('orders')])
        View My Orders
    @endcomponent

    Thanks for shopping with us!
    {{ config('app.name') }}
@endcomponent
