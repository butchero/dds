<x-site-layout :title="$title" :description="$description ?? null" :canonical="$canonical ?? null" :breadcrumbs="$breadcrumbs">
    @isset($category)
        <div class="px-2.5">
            <h1 class="mb-2 text-lg font-medium tracking-tight text-[#444444]">{{ $title }}</h1>
            @if (filled($category->description))
                <p class="mb-3 whitespace-pre-line">{{ $category->description }}</p>
            @endif
        </div>
        @if (($categoryBox['parent'] ?? null) !== null && $categoryBox['children']->isEmpty())
            <div class="mb-4 border border-[#e3e3e3] bg-white lg:hidden">
                <div class="border-b border-[#ededed] px-[10px] py-2 text-sm text-[#ff4e11]">{{ __('Subcategorii') }}</div>
                <x-category-siblings :parent="$categoryBox['parent']" :siblings="$categoryBox['siblings']" :current="$categoryBox['current']" />
            </div>
        @endif
        @if (($categoryFilters['hasFilters'] ?? false) === true)
            <div class="mb-4 lg:hidden">
                <details class="border border-[#e3e3e3] bg-white p-3">
                    <summary class="cursor-pointer font-semibold text-brand">{{ __('Filtre') }}</summary>
                    <div class="mt-3">
                        <x-category-filters :panel="$categoryFilters" />
                    </div>
                </details>
            </div>
        @endif
    @else
        <h1 class="mb-2 text-lg font-medium tracking-tight text-brand">{{ $title }}</h1>
    @endisset
    <div class="mb-4 flex flex-wrap items-center justify-end gap-2">
        <span class="text-sm text-[#444444]">{{ __('Afișează') }}</span>
        <div
            data-catalog-layout-toggle
            data-grid-url="{{ request()->fullUrlWithQuery(['afisare' => 'grila']) }}"
            data-list-url="{{ request()->fullUrlWithQuery(['afisare' => 'lista']) }}"
            data-layout="{{ $catalogLayout }}"
            data-grid-label="{{ __('Grilă') }}"
            data-list-label="{{ __('Unul sub altul') }}"
        ></div>
    </div>
    <div
        data-catalog-layout="{{ $catalogLayout }}"
        @class([
            'grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3' => $catalogLayout !== 'list',
            'flex flex-col gap-4' => $catalogLayout === 'list',
        ])
    >
        @foreach ($products as $product)
            <x-product-card :product="$product" :layout="$catalogLayout" />
        @endforeach
    </div>
    @if ($products->isEmpty())
        <p>{{ __('Nu am gasit produse.') }}</p>
    @endif
</x-site-layout>
