@props(['product'])

@php
    $logos = [
        'ARISTON' => '/images/producatori/ariston.jpg',
        'BUDERUS' => '/images/producatori/buderus.jpg',
    ];
    $logo = $logos[$product->manufacturer] ?? null;
@endphp

<div class="min-w-0 p-[2px] text-center">
    <div class="inline-flex items-start gap-[4px] border border-[#e3e3e3] bg-white p-[4px]">
        <a href="{{ route('product', $product) }}" title="{{ $product->name }}" class="flex h-[80px] w-[95px] items-center justify-center">
            @if ($product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="max-h-[80px] max-w-[95px]">
            @endif
        </a>
        @if ($logo)
            <span><img src="{{ $logo }}" alt="{{ $product->manufacturer }}"></span>
        @endif
    </div>
    <div class="mx-auto mt-[5px] flex h-[30px] items-start justify-center">
        <a href="{{ route('product', $product) }}" title="{{ $product->name }}" class="leading-[13px] text-black no-underline hover:text-submeniu hover:underline">{{ $product->name }}</a>
    </div>
    @if ($product->category?->slug)
        <a href="{{ route('category', $product->category) }}" class="text-estompat underline">Mai multe produse din <br> aceasta categorie</a>
    @endif
</div>
