<x-site-layout :title="$heading" :suffix="false" :image="$banners[0]['url'] ?? null">
    @php
        $hero = $banners[0] ?? null;
        $brandLoop = $brands === [] ? [] : array_merge(...array_fill(0, 6, $brands));
        $brand = (string) \App\Models\SiteSetting::get('title_suffix');
        $foundedYear = (string) \App\Models\SiteSetting::get('founded_year');
        $office = \App\Support\ContactOffice::present();
        $addressLine = trim(implode(', ', array_filter([
            filled($office['city']) ? __('Orașul :city', ['city' => $office['city']]) : null,
            $office['street'] ?: null,
        ])));
    @endphp

    @if ($hero)
        <section class="relative isolate min-h-56 overflow-hidden rounded-lg bg-[#0a3d86] bg-cover bg-right sm:min-h-72" style="background-image: url('{{ $hero['url'] }}')">
            <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/50 to-black/10"></div>
            <div class="relative flex max-w-xl flex-col gap-4 px-6 py-8 text-white sm:py-10">
                <h1 class="text-3xl font-medium tracking-tight text-balance sm:text-4xl sm:leading-tight">{{ $heading }}</h1>
                <p class="text-base leading-6 text-white/90">{{ __('Bucurandu-ne de o reputatie foarte buna in randul clientilor nostri, suntem siguri ca produsele si serviciile companiei noastre va vor oferi o satisfactie garantata.') }}</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('login') }}" class="inline-flex h-10 items-center justify-center rounded-lg bg-brand px-4 text-sm font-medium text-white no-underline transition-all outline-none hover:bg-brand/90 focus-visible:ring-3 focus-visible:ring-white/70">{{ __('Programează o revizie') }}</a>
                    <a href="{{ route('search') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-white/80 bg-white px-4 text-sm font-medium text-[#444444] no-underline transition-all outline-none hover:bg-white/90 focus-visible:ring-3 focus-visible:ring-white/70">{{ __('Vezi produsele') }}</a>
                </div>
            </div>
        </section>
    @endif

    @if ($brandLoop !== [])
    <div class="group mt-3 overflow-hidden border-y border-hairline bg-white py-3">
        <div class="flex w-max animate-marquee group-hover:[animation-play-state:paused] motion-reduce:animate-none">
            @foreach ([1, 2] as $copy)
                <div class="flex shrink-0 items-center gap-12 pr-12" @if ($copy === 2) aria-hidden="true" @endif>
                    @foreach ($brandLoop as $brandItem)
                        <a href="{{ route('manufacturer', $brandItem['slug']) }}" class="flex h-12 items-center">
                            <img src="{{ $brandItem['src'] }}" alt="{{ $copy === 1 ? $brandItem['name'] : '' }}" class="h-12 w-auto max-w-40 object-contain">
                        </a>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="mt-4">
        @unless ($hero)
            <h1 class="mb-2 text-lg font-medium tracking-tight text-brand">{{ $heading }}</h1>
        @endunless
        <div class="p-[5px]">
            <p class="mb-3">{!! __('Înființată în anul :year, <strong>:brand</strong> prestează următoarele servicii:', ['year' => $foundedYear, 'brand' => e($brand)]) !!}</p>
            <ul class="mb-3 list-disc pl-10">
                <li>{!! __('Comercializarea si montarea de <strong>centrale termice</strong> (Buderus, ACV, Ariston, Ferolli, etc.) si <strong>aparate de aer conditionat</strong> (Toshiba, Daikin, Ferroli).') !!}</li>
                <li>{{ __('Service centrale termice si aparate de aer conditionat') }}</li>
                <li>{{ __('Autorizatii de functionare si verificari tehnice periodice conform ISCIR (AF si VTP) pentru toata gama de putere termica la centralele termice.') }}</li>
            </ul>
            @unless ($hero)
                <p class="mb-3">{{ __('Bucurandu-ne de o reputatie foarte buna in randul clientilor nostri, suntem siguri ca produsele si serviciile companiei noastre va vor oferi o satisfactie garantata.') }}</p>
            @endunless
            @if (filled($addressLine))
                <p>{{ __('Adresa firmă: :address', ['address' => $addressLine]) }}</p>
            @endif
        </div>
    </div>

    @if ($products->isNotEmpty())
        <div class="mt-4 rounded-[7px] border border-hairline py-[10px]">
            <div class="mt-[2px] ml-[10px] text-lg font-medium tracking-tight">
                <span class="text-brand">{{ __('ULTIMELE') }}</span> <span class="text-link">{{ __('PRODUSE') }}</span>
            </div>
            @foreach ($products->chunk(3) as $row)
                <div @class(['grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-3 xl:grid-cols-3', 'mt-[15px]' => $loop->first])>
                    @foreach ($row as $product)
                        <x-product-card :product="$product" :bordered="false" />
                    @endforeach
                </div>
                @unless ($loop->last)
                    <div class="mt-[10px]"></div>
                    <div class="dashed-line mx-[5px]"></div>
                    <div class="mt-[15px]"></div>
                @endunless
            @endforeach
        </div>
    @endif

    <div class="p-[5px] text-right">
        <a href="#top" class="inline-flex items-center gap-[3px] no-underline">
            <span class="text-sm font-semibold text-faded underline">{{ __('top') }}</span>
            <span class="flex h-[11px] w-[11px] items-center justify-center rounded-[2px] bg-brand">
                <svg width="7" height="7" viewBox="0 0 7 7" aria-hidden="true"><path d="M3.5 1.3 6.2 5H.8z" fill="#fff"></path></svg>
            </span>
        </a>
    </div>
</x-site-layout>
