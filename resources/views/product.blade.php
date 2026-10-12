<x-site-layout :title="$product->name" :description="$product->excerpt ?: $product->description" :image="$product->image ?: $product->images->first()?->path" :breadcrumbs="$breadcrumbs">
    <h1 class="mb-2 text-lg font-medium tracking-tight text-brand">{{ $product->name }}</h1>
    <div data-product-view>
        <script type="application/json" data-product-payload>@json($gallery, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR)</script>
        <div data-product-price>
            <x-product-price :product="$product" />
        </div>
    </div>
</x-site-layout>
