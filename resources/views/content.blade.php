<x-site-layout :title="$title" :description="$description ?? null" :image="$image ?? null" :type="$type ?? 'website'" :breadcrumbs="$breadcrumbs">
    <h1 class="mb-2 text-lg font-medium tracking-tight text-brand">{{ $title }}</h1>
    @if (! empty($excerpt))
        <p>{{ $excerpt }}</p>
    @endif
    <x-blocks :blocks="$blocks" />
</x-site-layout>
