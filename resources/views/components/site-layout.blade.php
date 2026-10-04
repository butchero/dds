@props(['title' => null])

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' — '.config('app.name') : config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="m-0 bg-white font-tahoma text-[11px] text-[#444444]">
    <div id="top" class="mx-auto w-full max-w-7xl bg-white px-3 pb-[10px] sm:px-4 lg:px-6">
        <div class="pr-5 pt-[5px] text-right">
            <p class="m-0 text-[9px] font-bold leading-[10px] text-[#b7b7b7]">Centrale termice Constanta - aparate aer conditionat Constanta</p>
        </div>

        <div data-mobile-menu>
            <div class="mt-2 flex flex-col items-center gap-3 sm:flex-row sm:items-end sm:justify-between">
                <a href="{{ route('home') }}" class="mb-0 block shrink-0 sm:mb-2" v-pre>
                    <img src="/images/logo.svg" width="155" height="79" alt="DDS Services Group">
                </a>
                <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded border border-[#d5d5d5] text-portocaliu sm:hidden" aria-label="Meniu" @@click="open = !open">
                    <span class="block h-0.5 w-4 bg-current shadow-[0_-5px_0_currentColor,0_5px_0_currentColor]"></span>
                </button>
                @if ($site['menuPosition'] === 'top')
                    <nav class="relative z-20 -mb-px hidden max-w-full justify-center font-arial sm:flex sm:justify-end" v-pre>
                        @foreach ($site['menu'] as $item)
                            <a href="{{ $item->url }}" @class(['tab', 'tab-activ' => \App\Support\SiteCatalog::isCurrent($item->url), 'mr-[10px]' => $loop->last])>{{ $item->label }}</a>
                        @endforeach
                    </nav>
                @endif
            </div>

            <div class="mt-2 flex flex-col border border-marginelight font-arial sm:hidden" style="display: none" v-show="open">
                @foreach ($site['menu'] as $item)
                    <a href="{{ $item->url }}" class="border-b border-marginelight px-3 py-2 text-portocaliu no-underline" v-pre>{{ $item->label }}</a>
                @endforeach
                @auth
                    <a href="{{ route('account.home') }}" class="px-3 py-2 text-portocaliu no-underline" v-pre>Contul meu</a>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 text-portocaliu no-underline" v-pre>Autentificare</a>
                @endauth
            </div>
        </div>

        <div class="bara-cautare flex min-h-[35px] flex-col gap-2 px-3 py-2 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-4 sm:py-[5px]">
            <form class="relative z-10 flex items-center gap-2" action="{{ route('search') }}" method="get">
                <span class="font-arial text-[11px] font-bold tracking-[1px] text-portocaliu">Cauta:</span>
                <input type="text" name="s" value="{{ request('s') }}" maxlength="30" aria-label="Cauta in site" class="h-[20px] w-[117px] max-w-full border border-[#a4a4a4] px-[3px] text-[11px] outline-none">
                <button type="submit" aria-label="Cauta" class="inline-flex h-[20px] w-[22px] shrink-0 items-center justify-center rounded-[2px] border border-[#a4a4a4] bg-[#f4f4f4] text-[#444444] hover:bg-white">
                    <svg class="h-[13px] w-[13px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>
                </button>
            </form>
            <div class="relative z-10 flex flex-wrap items-center gap-x-[10px] gap-y-1 font-arial text-[11px] tracking-[0.6px] text-portocaliu">
                <a href="/servicii/montaj-centrala" class="hover:underline">Care centrala termica mi se potriveste ?</a>
                <span class="hidden h-[18px] w-px bg-[#dcdcdc] sm:block"></span>
                <a href="/servicii/revizie-centrala" class="hover:underline">Garantie si service</a>
                <span class="hidden h-[18px] w-px bg-[#dcdcdc] sm:block"></span>
                <a href="/servicii/contact" class="hover:underline">Intrebari frecvente</a>
            </div>
        </div>

        <div class="flex flex-col items-start gap-4 pt-3 md:flex-row md:flex-wrap lg:flex-nowrap lg:gap-0 lg:pt-[5px]">
            <aside class="order-2 w-full shrink-0 md:w-[calc(50%-8px)] lg:order-1 lg:w-60 lg:pr-[5px]">
                @if ($site['menuPosition'] === 'left')
                    <nav class="mb-[5px] flex">
                        <div class="w-[6px] shrink-0 pt-[63px]">
                            <div class="h-[84px] w-[6px] rounded-l-[5px] bg-[#fe5013]"></div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex h-[37px] items-center justify-center rounded-t-[7px] border border-b-0 border-[#ededed] bg-gradient-to-b from-white to-[#f7f7f7]">
                                <span class="font-arial text-[12px] text-[#ff4e11]">MENIU</span>
                            </div>
                            <div class="border-x border-marginelight pt-[7px]">
                                @foreach ($site['menu'] as $item)
                                    <div @class(['flex px-[10px]', 'pb-[7px]' => $loop->last])>
                                        <span class="w-[18px] shrink-0 pt-[2px]"><img src="/images/bullet-orange.svg" width="13" height="12" alt=""></span>
                                        <a href="{{ $item->url }}" class="text-[12px] font-bold leading-[14px] text-meniu no-underline hover:text-albastru">{{ $item->label }}</a>
                                    </div>
                                    @unless ($loop->last)
                                        <div class="linie-punctata mx-auto my-[4px] w-[80%] max-w-[180px]"></div>
                                    @endunless
                                @endforeach
                            </div>
                            <div class="h-[8px] rounded-b-[7px] border border-t-0 border-marginelight"></div>
                        </div>
                    </nav>
                @endif

                @if ($site['hasProducts'])
                    <div class="flex">
                        <div class="w-[6px] shrink-0 pt-[63px]">
                            <div class="h-[129px] w-[6px] rounded-l-[5px] bg-[#fe5013]"></div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex h-[37px] items-center rounded-t-[7px] border border-b-0 border-[#ededed] bg-gradient-to-b from-white to-[#f7f7f7]">
                                <img src="/images/flame.svg" width="16" height="20" class="ml-[12px]" alt="">
                                <span class="ml-[6px] font-arial text-[12px] text-[#ff4e11]">CATEGORII</span>
                            </div>
                            <div class="border-x border-marginelight pt-[7px]">
                                @foreach ($site['productCategories'] as $category)
                                    <div class="flex px-[10px]">
                                        <span class="w-[18px] shrink-0 pt-[2px]"><img src="/images/bullet-orange.svg" width="13" height="12" alt=""></span>
                                        <a href="{{ route('category', $category) }}" class="text-[12px] font-bold leading-[14px] text-meniu no-underline hover:text-albastru">{{ $category->name }}</a>
                                    </div>
                                    <div class="linie-punctata mx-auto my-[4px] w-[80%] max-w-[180px]"></div>
                                    @foreach ($category->children as $child)
                                        @php($isLastChild = $loop->parent->last && $loop->last)
                                        <div @class(['flex px-[10px]', 'pb-[7px]' => $isLastChild])>
                                            <span class="w-[25px] shrink-0"></span>
                                            <a href="{{ route('category', $child) }}" class="leading-[13px] text-submeniu no-underline hover:text-albastru hover:underline">{{ $child->name }}</a>
                                        </div>
                                        @unless ($isLastChild)
                                            <div class="linie-punctata mx-auto my-[4px] w-[80%] max-w-[180px]"></div>
                                        @endunless
                                    @endforeach
                                @endforeach
                            </div>
                            <div class="h-[8px] rounded-b-[7px] border border-t-0 border-marginelight"></div>
                        </div>
                    </div>
                @endif

                @foreach ($site['serviceCategories'] as $category)
                    <div class="mt-[5px] flex">
                        <div class="w-[6px] shrink-0 pt-[63px]">
                            <div @class(['h-[84px] w-[6px] rounded-l-[5px]', 'bg-[#8c8c8c]' => $site['hasProducts'], 'bg-[#fe5013]' => ! $site['hasProducts']])></div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex h-[37px] items-center justify-center rounded-t-[7px] border border-b-0 border-[#ededed] bg-gradient-to-b from-white to-[#f7f7f7]">
                                <span class="font-arial text-[12px] text-[#ff4e11]">{{ mb_strtoupper($category->name, 'UTF-8') }}</span>
                            </div>
                            <div class="border-x border-marginelight pt-[7px]">
                                @foreach ($category->services as $service)
                                    <div @class(['flex px-[10px]', 'pb-[7px]' => $loop->last])>
                                        <span class="w-[18px] shrink-0 pt-[2px]"><img src="{{ $site['hasProducts'] ? '/images/bullet-gray.svg' : '/images/bullet-orange.svg' }}" width="13" height="12" alt=""></span>
                                        <a href="{{ route('service', $service) }}" class="text-[12px] font-bold leading-[14px] text-meniu no-underline hover:text-albastru">{{ $service->name }}</a>
                                    </div>
                                    @unless ($loop->last)
                                        <div class="linie-punctata mx-auto my-[4px] w-[80%] max-w-[180px]"></div>
                                    @endunless
                                @endforeach
                            </div>
                            <div class="h-[8px] rounded-b-[7px] border border-t-0 border-marginelight"></div>
                        </div>
                    </div>
                @endforeach

                @if (count($site['manufacturers']))
                    <div class="mt-[5px] flex">
                        <div class="w-[6px] shrink-0 pt-[63px]">
                            <div class="h-[84px] w-[6px] rounded-l-[5px] bg-[#8c8c8c]"></div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex h-[37px] items-center justify-center rounded-t-[7px] border border-b-0 border-[#ededed] bg-gradient-to-b from-white to-[#f7f7f7]">
                                <span class="font-arial text-[12px] text-[#ff4e11]">PRODUCATORI</span>
                            </div>
                            <div class="border-x border-marginelight pt-[7px]">
                                @foreach ($site['manufacturers'] as $maker)
                                    <div class="flex px-[10px]">
                                        <span class="w-[18px] shrink-0 pt-[2px]"><img src="/images/bullet-gray.svg" width="13" height="12" alt=""></span>
                                        <span class="leading-[14px] text-[#62879f]"><a href="{{ route('search', ['producator' => $maker['name']]) }}" class="font-bold text-[#6f6a68] no-underline hover:text-albastru hover:underline">{{ $maker['name'] }}</a> ({{ $maker['total'] }})</span>
                                    </div>
                                    <div class="linie-punctata mx-auto my-[4px] w-[80%] max-w-[180px]"></div>
                                @endforeach
                                <div class="px-[10px] pb-[7px] pt-[5px] text-right">
                                    <a href="{{ route('search') }}" class="text-[#444444] no-underline">...Toti producatorii</a>
                                </div>
                            </div>
                            <div class="h-[8px] rounded-b-[7px] border border-t-0 border-marginelight"></div>
                        </div>
                    </div>
                @endif
            </aside>

            <main class="order-1 w-full min-w-0 lg:order-2 lg:w-auto lg:flex-1">
                {{ $slot }}
            </main>

            <aside class="order-3 w-full shrink-0 md:w-[calc(50%-8px)] lg:w-56 lg:px-[5px]">
                <div class="w-full">
                    <div class="h-[8px] rounded-t-[7px] border border-b-0 border-marginelight bg-white"></div>
                    <div class="border-x border-marginelight bg-white px-[2px] py-[2px]">
                        <div class="p-[2px] text-center font-arial text-[13px] text-portocaliu">STIRI</div>
                        @foreach ($site['news'] as $item)
                            <div class="p-[5px] text-center text-[11px] font-bold text-albastru">
                                <a href="{{ route('news', $item) }}" class="text-albastru no-underline hover:underline">{{ $item->title }}</a>
                            </div>
                            <div @class(['px-[5px] text-[11px] leading-[14px] text-[#716f6f]', 'pb-[5px]' => $loop->last])>{{ $item->excerpt }}</div>
                        @endforeach
                    </div>
                    <img src="/images/box-bottom-right.svg" width="164" height="33" alt="" class="block h-auto w-full">
                </div>

                @if (count($site['promos']))
                    <div class="mt-[5px] w-full">
                        <div class="h-[8px] rounded-t-[7px] border border-b-0 border-marginelight bg-white"></div>
                        <div class="border-x border-marginelight bg-white px-[2px] py-[2px]">
                            <div class="p-[2px] text-center font-arial text-[13px] text-portocaliu">SUPER PROMOTIE</div>
                            @foreach ($site['promos'] as $product)
                                <div class="p-[5px] text-center">
                                    <a href="{{ route('product', $product['slug']) }}" class="inline-block">
                                        <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" class="mx-auto max-w-full border border-[#e3e3e3] bg-white p-[10px]">
                                    </a>
                                    <div class="mt-[5px]">
                                        <a href="{{ route('product', $product['slug']) }}" class="text-[10px] font-bold text-portocaliu no-underline hover:text-submeniu">{{ $product['name'] }}</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <img src="/images/box-bottom-right.svg" width="164" height="33" alt="" class="block h-auto w-full">
                    </div>
                @endif
            </aside>
        </div>

        <div class="h-[20px]"></div>
        <div class="h-px bg-marginelight"></div>
        <div class="p-[10px] text-center leading-[20px]">
            <a href="/categorie/centrale-termice" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Centrale termice</a>&nbsp;-&nbsp;
            <a href="{{ route('search') }}" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Producatori</a>&nbsp;-&nbsp;
            <a href="/servicii/contact" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Intrebari Frecvente</a>&nbsp;-&nbsp;
            <a href="/servicii/despre-noi" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Despre Noi</a>&nbsp;-&nbsp;
            <a href="/servicii/despre-noi" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Termeni si conditii</a>&nbsp;-&nbsp;
            <a href="{{ route('search') }}" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Toate cautarile</a>&nbsp;-&nbsp;
            <a href="{{ route('home') }}" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Sitemap</a>&nbsp;-&nbsp;
            <a href="/servicii/contact" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Contact</a>
        </div>
        <div class="text-center text-[11px] text-[#716f6f]">
            <p class="mb-[11px]">Citeste <a href="{{ route('privacy') }}" class="text-albastru">Politica de Confidentialitate</a> si continuarea navigarii pe acest site implica acceptarea <a href="{{ route('cookies') }}" class="text-albastru">Politicii de Cookies</a>.</p>
            <p class="mb-[11px]">
                <button type="button" class="text-albastru underline" data-cookies-open>Setări cookie-uri</button>
            </p>
            <p class="mb-[11px]">
                <a href="https://www.cazare-pe-litoral.ro" title="Cazare pe litoral" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Cazare pe litoral</a>
                &nbsp;-&nbsp;
                <a href="https://www.magazin-blanuri.ro" title="Magazin blanuri - haine din blana" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Magazin blanuri</a>
            </p>
        </div>
        <div class="h-px bg-marginelight"></div>
        <div class="p-[10px] text-center">
            un produs proaspat oferit de <a href="https://www.layerzero.ro" title="Web Design" class="text-albastru">layerzero.ro</a>
        </div>
        <div class="text-center">&copy; 2008 DDS Services Group</div>

        <div data-cookies>
            <div class="fixed inset-x-0 bottom-0 z-50 px-3 pb-3" style="display: none" v-show="visible">
                <div class="mx-auto max-w-7xl border border-[#e2e2e2] bg-white p-4 shadow-lg">
                    <p class="m-0 font-arial text-[13px] text-portocaliu">Cookie-uri</p>
                    <p class="mt-2 mb-0 leading-[16px] text-[#444444]">
                        Folosim doar cookie-uri necesare pentru sesiune și securitatea formularelor. Nu încărcăm cookie-uri de statistică sau marketing înainte de acordul tău.
                        Detalii în <a href="{{ route('cookies') }}" class="text-albastru">Politica de cookie-uri</a>
                        și <a href="{{ route('privacy') }}" class="text-albastru">Politica de confidențialitate</a>.
                    </p>
                    <div class="mt-3 border border-marginelight p-3 leading-[16px]" style="display: none" v-show="details">
                        <p class="m-0"><strong>Necesare</strong> — mereu active. Țin sesiunea de autentificare, protecția formularelor și alegerea făcută aici. Nu pot fi oprite.</p>
                        <p class="mt-2 mb-0"><strong>Statistici</strong> — momentan nu folosim astfel de cookie-uri. Dacă le vom adăuga, vor porni doar după „Acceptă toate”.</p>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button" class="bg-portocaliu px-3 py-1 text-white" @@click="save('all')">Acceptă toate</button>
                        <button type="button" class="border border-[#a4a4a4] bg-white px-3 py-1" @@click="save('necessary')">Doar necesare</button>
                        <button type="button" class="px-3 py-1 text-albastru underline" @@click="details = !details">@{{ details ? 'Ascunde detaliile' : 'Setări' }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
