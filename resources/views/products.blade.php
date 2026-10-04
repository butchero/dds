<x-site-layout :title="$title">
    <h1 class="mb-[5px] font-arial text-[13px] text-portocaliu">{{ $title }}</h1>
    <div class="grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-3 xl:grid-cols-3">
        @foreach ($products as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
    @if ($products->isEmpty())
        <p>Nu am gasit produse.</p>
    @endif
</x-site-layout>
