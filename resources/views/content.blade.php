<x-site-layout :title="$title">
    <h1 class="mb-[5px] font-arial text-[13px] text-portocaliu">{{ $title }}</h1>
    @if (! empty($excerpt))
        <p class="leading-[15px]">{{ $excerpt }}</p>
    @endif
    <x-blocks :blocks="$blocks" />
</x-site-layout>
