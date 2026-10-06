@props(['product'])

@php
    $price = \App\Support\ProductPrice::from($product);
@endphp

@if ($price || $product->quantity !== null)
    <div class="mt-1">
        @if ($price)
            <p class="m-0 text-sm font-semibold text-brand">{{ $price->gross }}</p>
            <p class="m-0 text-xs text-faded">{{ __('Preț fără TVA: :net · TVA :rate', ['net' => $price->net, 'rate' => $price->vatRate]) }}</p>
        @endif
        @if ($product->quantity !== null)
            <p @class([
                'm-0 text-xs',
                'text-brand' => $product->quantity === 0,
                'text-[#444444]' => $product->quantity > 0,
            ])>{{ $product->quantity > 0 ? __('Stoc: :count', ['count' => $product->quantity]) : __('Stoc epuizat') }}</p>
        @endif
    </div>
@endif
