@props(['blocks' => []])

<div class="space-y-4">
    @foreach ($blocks as $block)
        <section>
            @if (($block['type'] ?? null) === 'heading')
                <h2 class="m-0 font-arial text-[13px] text-portocaliu">{{ $block['data']['text'] ?? '' }}</h2>
            @elseif (($block['type'] ?? null) === 'text')
                <div class="leading-[15px] whitespace-pre-line">{{ $block['data']['body'] ?? '' }}</div>
            @elseif (($block['type'] ?? null) === 'image')
                <img src="{{ $block['data']['url'] ?? '' }}" alt="{{ $block['data']['alt'] ?? '' }}" class="max-w-full">
            @elseif (($block['type'] ?? null) === 'text_image')
                <div class="flex flex-col gap-3 sm:flex-row">
                    <img src="{{ $block['data']['url'] ?? '' }}" alt="{{ $block['data']['alt'] ?? '' }}" class="max-w-full sm:w-1/2">
                    <div class="whitespace-pre-line leading-[15px]">{{ $block['data']['body'] ?? '' }}</div>
                </div>
            @elseif (($block['type'] ?? null) === 'button')
                <a href="{{ $block['data']['url'] ?? '#' }}" class="inline-flex bg-portocaliu px-4 py-2 text-white no-underline">{{ $block['data']['label'] ?? '' }}</a>
            @endif
        </section>
    @endforeach
</div>
