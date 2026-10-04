<x-site-layout title="Centrale termice Constanta - aparate aer conditionat Constanta">
    @if (count($banners))
        <div data-banner-rotator class="flex items-stretch">
            <div id="rotator" class="min-w-0 flex-1" :class="{ pornit: started }">
                @foreach ($banners as $banner)
                    <a href="{{ $banner['link'] ?: '#' }}">
                        <img src="{{ $banner['url'] }}" alt="{{ $banner['title'] }}" :class="{ activ: current === {{ $loop->index }} }">
                    </a>
                @endforeach
            </div>
            <div class="flex w-[28px] shrink-0 items-center justify-center bg-[url('/images/promo-margine.svg')] bg-[length:100%_100%] bg-no-repeat">
                <div class="w-[16px]">
                    @foreach ($banners as $banner)
                        <button type="button" class="mb-[1px] block w-[14px] border text-center text-[12px] font-bold leading-[14px] text-white" :class="current === {{ $loop->index }} ? 'border-white bg-[#f49591]' : 'border-transparent'" @@click="choose({{ $loop->index }})">{{ $loop->iteration }}</button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="mt-[5px]">
        <h1 class="mb-[5px] p-0 font-arial text-[13px] tracking-[0.3px] text-portocaliu">Centrale termice Constanta - aparate aer conditionat Constanta</h1>
        <div class="p-[5px] leading-[15px]">
            <p class="mb-[11px]">Infiintata in anul 1995, <strong>DDS Services Group</strong> S.R.L. presteaza urmatoarele servicii:</p>
            <ul class="mb-[11px] list-disc pl-[40px]">
                <li>Comercializarea si montarea de <strong>centrale termice</strong> (Buderus, ACV, Ariston, Ferolli, etc.) si <strong>aparate de aer conditionat</strong> (Toshiba, Daikin, Ferroli).</li>
                <li>Service centrale termice si aparate de aer conditionat</li>
                <li>Autorizatii de functionare si verificari tehnice periodice conform ISCIR (AF si VTP) pentru toata gama de putere termica la centralele termice.</li>
            </ul>
            <p class="mb-[11px]">Bucurandu-ne de o reputatie foarte buna in randul clientilor nostri, suntem siguri ca produsele si serviciile companiei noastre va vor oferi o satisfactie garantata.</p>
            Adresa firma: Orasul Constanta, Bd. Mamaia nr. 70, Bl. Bi 1, parter.
        </div>
    </div>

    @if ($products->isNotEmpty())
        <div class="mt-[5px] rounded-[7px] border border-marginelight py-[10px]">
            <div class="ml-[10px] mt-[2px] font-arial text-[13px] tracking-[0.3px]">
                <span class="text-portocaliu">ULTIMELE</span> <span class="text-albastru">PRODUSE</span>
            </div>
            @foreach ($products->chunk(3) as $row)
                <div @class(['grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-3 xl:grid-cols-3', 'mt-[15px]' => $loop->first])>
                    @foreach ($row as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
                @unless ($loop->last)
                    <div class="mt-[10px]"></div>
                    <div class="linie-intrerupta"></div>
                    <div class="mt-[15px]"></div>
                @endunless
            @endforeach
        </div>
    @endif

    <div class="p-[5px] text-right">
        <a href="#top" class="inline-flex items-center gap-[3px] no-underline">
            <span class="text-[11px] font-bold text-estompat underline">top</span>
            <span class="flex h-[11px] w-[11px] items-center justify-center rounded-[2px] bg-portocaliu">
                <svg width="7" height="7" viewBox="0 0 7 7" aria-hidden="true"><path d="M3.5 1.3 6.2 5H.8z" fill="#fff"></path></svg>
            </span>
        </a>
    </div>
</x-site-layout>
