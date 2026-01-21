@component('mail::message')
# Daily Sales Report 📊

@if($items->isEmpty())
No products were sold today.
@else
| Product | Quantity | Price |
|--------|----------|-------|
@foreach ($items as $item)
| {{ $item->product->name }} | {{ $item->quantity }} | ${{ $item->price }} |
@endforeach
@endif

Thanks,  
{{ config('app.name') }}
@endcomponent
