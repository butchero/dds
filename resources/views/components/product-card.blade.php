@props(['product', 'layout' => 'grid', 'bordered' => true])

@php
    $highlights = $product->listingHighlights();
    $listed = $layout === 'list';
@endphp

<article @class([
    'bg-white p-3',
    'border border-[#e3e3e3]' => $bordered,
    'flex h-full flex-col gap-2 text-center' => ! $listed,
    'flex items-start gap-4 text-left' => $listed,
])>
    <a
        href="{{ route('product', $product) }}"
        title="{{ $product->name }}"
        @class([
            'flex items-center justify-center',
            'h-[180px]' => ! $listed,
            'h-[140px] w-[140px] shrink-0' => $listed,
        ])
    >
        @if ($product->image_url)
            <img
                src="{{ $product->image_url }}"
                alt="{{ $product->name }}"
                @class([
                    'max-w-full',
                    'max-h-[180px]' => ! $listed,
                    'max-h-[140px]' => $listed,
                ])
            >
        @endif
    </a>
    <div @class(['flex min-w-0 flex-1 flex-col gap-2', 'items-center' => ! $listed, 'items-start' => $listed])>
        <a href="{{ route('product', $product) }}" title="{{ $product->name }}" class="text-sm leading-5 text-black no-underline hover:text-submenu hover:underline">{{ $product->name }}</a>
        @if ($highlights !== [])
            <ul class="m-0 flex list-none flex-col gap-0.5 p-0 text-xs text-faded">
                @foreach ($highlights as $highlight)
                    <li>{{ $highlight['name'] }}: {{ $highlight['value'] }}</li>
                @endforeach
            </ul>
        @endif
        <x-product-price :product="$product" />
    </div>
</article>
