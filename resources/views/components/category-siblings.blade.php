@props(['parent', 'siblings', 'current'])

<div class="pt-[7px]">
    <div class="flex px-[10px]">
        <span class="w-[18px] shrink-0 pt-[2px]"><img src="/images/bullet-orange.svg" width="13" height="12" alt=""></span>
        <a href="{{ route('category', $parent) }}" class="min-w-0 text-sm font-semibold leading-5 text-link no-underline hover:underline">{{ $parent->name }}</a>
    </div>
    <div class="dotted-line mx-auto my-[4px] w-[80%] max-w-[180px]"></div>
    @foreach ($siblings as $sibling)
        <div @class(['flex px-[10px]', 'pb-[7px]' => $loop->last])>
            <span class="w-[25px] shrink-0"></span>
            <a
                href="{{ route('category', $sibling) }}"
                @class([
                    'min-w-0 text-sm leading-5 no-underline hover:text-link',
                    'font-bold text-[#444444]' => $sibling->is($current),
                    'text-submenu hover:underline' => ! $sibling->is($current),
                ])
            >{{ $sibling->name }}</a>
        </div>
        @unless ($loop->last)
            <div class="dotted-line mx-auto my-[4px] w-[80%] max-w-[180px]"></div>
        @endunless
    @endforeach
</div>
