@props(['items'])

<nav aria-label="{{ __('Breadcrumb') }}" {{ $attributes->merge(['class' => 'mt-2.5 px-3 py-2']) }}>
    <ol class="flex flex-wrap items-center gap-2 text-sm text-[#716f6f]">
        @foreach ($items as $item)
            <li class="flex items-center gap-2">
                @unless ($loop->first)
                    <span aria-hidden="true">›</span>
                @endunless
                @if ($loop->last)
                    <span aria-current="page" class="text-[#444444]">{{ $item['label'] }}</span>
                @elseif (blank($item['url'] ?? null))
                    <span class="text-[#444444]">{{ $item['label'] }}</span>
                @else
                    <a href="{{ $item['url'] }}" class="text-link no-underline hover:underline">{{ $item['label'] }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
<script type="application/ld+json">{!! \App\Support\Breadcrumbs::schema($items) !!}</script>
