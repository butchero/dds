<x-site-layout :title="$product->name" :breadcrumbs="$breadcrumbs">
    <h1 class="mb-2 text-lg font-medium tracking-tight text-brand">{{ $product->name }}</h1>
    @if ($product->excerpt)
        <p>{{ $product->excerpt }}</p>
    @endif
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
        @if ($product->images->isNotEmpty() || $product->image_url)
            <div data-product-gallery>
                <img data-gallery-main src="{{ $product->images->first()->url ?? $product->image_url }}" alt="{{ $product->name }}" class="max-h-48 max-w-full border border-[#e3e3e3] bg-white p-[10px]">
                @if ($product->images->count() > 1)
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($product->images as $image)
                            <button type="button" data-gallery-thumb data-src="{{ $image->url }}" class="border border-[#e3e3e3] bg-white p-1" aria-label="{{ $product->name }}">
                                <img src="{{ $image->url }}" alt="" class="h-14 w-14 object-contain">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
        <x-product-price :product="$product" />
    </div>
    @if ($specifications->isNotEmpty())
        <table class="mt-4 w-full border-collapse text-sm">
            <tbody>
                @foreach ($specifications as $row)
                    <tr class="border-b border-[#e3e3e3]">
                        <th class="w-1/3 py-1 pr-3 text-left font-semibold text-[#444444]">{{ $row['name'] }}</th>
                        <td class="py-1">{{ $row['value'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    @if ($product->description)
        <div class="mt-4 whitespace-pre-line">{{ $product->description }}</div>
    @endif
    <script>
        document.querySelectorAll('[data-product-gallery]').forEach((gallery) => {
            const main = gallery.querySelector('[data-gallery-main]');

            gallery.querySelectorAll('[data-gallery-thumb]').forEach((thumb) => {
                thumb.addEventListener('click', () => {
                    main.src = thumb.dataset.src;
                });
            });
        });
    </script>
</x-site-layout>
