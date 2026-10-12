@props([
    'title' => null,
    'breadcrumbs' => null,
    'account' => false,
    'accountCurrent' => null,
    'description' => null,
    'image' => null,
    'index' => true,
    'type' => 'website',
    'suffix' => true,
    'canonical' => null,
    'wide' => false,
])

@php
    $meta = \App\Support\PageMeta::make(
        title: $title,
        description: $description,
        image: $image,
        index: (bool) $index,
        type: $type,
        suffix: (bool) $suffix,
        canonical: $canonical,
    );
@endphp

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-page-meta :meta="$meta" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="m-0 bg-white font-sans text-base leading-6 text-[#444444]">
    <div id="top" class="mx-auto w-full max-w-7xl bg-white px-3 pb-[10px] sm:px-4 lg:px-6">
        <div data-mobile-menu>
            <div class="mt-2 flex flex-col items-center gap-3 sm:flex-row sm:items-end sm:justify-between">
                <a href="{{ route('home') }}" class="mb-0 block shrink-0 sm:mb-2" v-pre>
                    <img src="/images/logo.svg" width="155" height="79" alt="{{ \App\Models\SiteSetting::get('title_suffix') }}">
                </a>
                <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded border border-[#d5d5d5] text-brand sm:hidden" aria-label="{{ __('Meniu') }}" @@click="open = !open">
                    <span class="block h-0.5 w-4 bg-current shadow-[0_-5px_0_currentColor,0_5px_0_currentColor]"></span>
                </button>
                <div class="hidden flex-col items-end self-stretch sm:flex">
                    <div class="flex w-full flex-1 items-center justify-end pr-[calc(38px_+_10px)]">
                        <div class="-translate-y-1">
                            <x-header-account-links layout="desktop" />
                        </div>
                    </div>
                    @if ($site['menuPosition'] === 'top')
                        <nav class="relative z-20 -mb-px flex max-w-full shrink-0 justify-end" v-pre>
                            @foreach ($site['menu'] as $item)
                                <a href="{{ $item->url }}" @class(['tab', 'tab-active' => \App\Support\SiteCatalog::isCurrent($item->url), 'mr-[10px]' => $loop->last])>{{ $item->label }}</a>
                            @endforeach
                        </nav>
                    @endif
                </div>
            </div>

            <div class="mt-2 flex flex-col border border-hairline text-sm sm:hidden" style="display: none" v-show="open">
                @foreach ($site['menu'] as $item)
                    <a href="{{ $item->url }}" class="border-b border-hairline px-3 py-2 text-brand no-underline" v-pre>{{ $item->label }}</a>
                @endforeach
                <x-header-account-links layout="menu" />
            </div>
        </div>

        <div class="search-bar flex min-h-[35px] flex-col gap-2 px-3 py-2 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-4 sm:py-[5px]">
            <form class="relative z-10 flex items-center gap-2" action="{{ route('search') }}" method="get">
                <span class="text-sm font-semibold tracking-wide text-brand">{{ __('Cauta:') }}</span>
                <div class="w-64 max-w-full">
                    <x-ui.input type="text" name="s" value="{{ request('s') }}" maxlength="30" aria-label="{{ __('Cauta in site') }}" class="h-8!" />
                </div>
                <button type="submit" aria-label="{{ __('Cauta') }}" class="inline-flex size-8 shrink-0 items-center justify-center rounded-md border border-input bg-white text-[#444444] shadow-xs transition-all outline-none hover:bg-muted focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50">
                    <svg class="h-[13px] w-[13px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>
                </button>
            </form>
            <div class="relative z-10 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-brand">
                <a href="/servicii/montaj-centrala" class="hover:underline">{{ __('Care centrala termica mi se potriveste ?') }}</a>
                <span class="hidden h-[18px] w-px bg-[#dcdcdc] sm:block"></span>
                <a href="/servicii/revizie-centrala" class="hover:underline">{{ __('Garantie si service') }}</a>
                <span class="hidden h-[18px] w-px bg-[#dcdcdc] sm:block"></span>
                <a href="/servicii/contact" class="hover:underline">{{ __('Intrebari frecvente') }}</a>
            </div>
        </div>

        @if (is_array($breadcrumbs) && count($breadcrumbs) > 1)
            <x-breadcrumbs :items="$breadcrumbs" />
        @endif

        <div @class([
            'pt-2.5',
            'flex flex-col items-start gap-2.5 md:flex-row md:flex-wrap lg:flex-nowrap lg:gap-0' => ! $wide,
        ])>
            @unless ($wide)
            <aside class="order-2 w-full shrink-0 md:w-[calc(50%-5px)] lg:order-1 lg:w-60 lg:pr-2.5">
                @if ($account)
                    <nav class="flex" aria-label="{{ __('Secțiunile contului') }}">
                        <div class="w-[6px] shrink-0 pt-[70px]">
                            <div class="h-[129px] w-[6px] rounded-l-[5px] bg-[#fe5013]"></div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex h-11 items-center rounded-t-[7px] border border-b-0 border-[#ededed] bg-gradient-to-b from-white to-[#f7f7f7]">
                                <span class="ml-[12px] text-sm text-[#ff4e11]">{{ __('CONTUL MEU') }}</span>
                            </div>
                            <div class="border-x border-hairline pt-[7px]">
                                @foreach ([
                                    'profile' => [__('Datele mele'), route('account.home')],
                                    'addresses' => [__('Adrese'), route('account.addresses')],
                                    'equipment' => [__('Echipamente'), route('account.equipment')],
                                    'billing' => [__('Facturare'), route('account.billing')],
                                    'appointments' => [__('Programări'), route('account.appointments')],
                                ] as $key => [$label, $url])
                                    <div @class(['flex px-[10px]', 'pb-[7px]' => $loop->last])>
                                        <a
                                            href="{{ $url }}"
                                            @class([
                                                'text-sm leading-5 no-underline hover:text-link',
                                                'font-bold text-[#444444]' => $accountCurrent === $key,
                                                'font-semibold text-menu' => $accountCurrent !== $key,
                                            ])
                                            @if ($accountCurrent === $key) aria-current="page" @endif
                                        >{{ $label }}</a>
                                    </div>
                                    @unless ($loop->last)
                                        <div class="dotted-line mx-auto my-[4px] w-[80%] max-w-[180px]"></div>
                                    @endunless
                                @endforeach
                            </div>
                            <div class="h-[8px] rounded-b-[7px] border border-t-0 border-hairline"></div>
                        </div>
                    </nav>
                @else
                @if ($site['menuPosition'] === 'left')
                    <nav class="mb-2.5 flex">
                        <div class="w-[6px] shrink-0 pt-[70px]">
                            <div class="h-[84px] w-[6px] rounded-l-[5px] bg-[#fe5013]"></div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex h-11 items-center justify-center rounded-t-[7px] border border-b-0 border-[#ededed] bg-gradient-to-b from-white to-[#f7f7f7]">
                                <span class="text-sm text-[#ff4e11]">{{ __('MENIU') }}</span>
                            </div>
                            <div class="border-x border-hairline pt-[7px]">
                                @foreach ($site['menu'] as $item)
                                    <div @class(['flex px-[10px]', 'pb-[7px]' => $loop->last])>
                                        <a href="{{ $item->url }}" class="text-sm font-semibold leading-5 text-menu no-underline hover:text-link">{{ $item->label }}</a>
                                    </div>
                                    @unless ($loop->last)
                                        <div class="dotted-line mx-auto my-[4px] w-[80%] max-w-[180px]"></div>
                                    @endunless
                                @endforeach
                            </div>
                            <div class="h-[8px] rounded-b-[7px] border border-t-0 border-hairline"></div>
                        </div>
                    </nav>
                @endif

                @if ($site['hasProducts'])
                    @php
                        $onCategory = isset($categoryBox);
                        $listsEveryCategory = $onCategory && ($categoryBox['listsEveryCategory'] ?? false);
                        $boxTitle = $onCategory ? $categoryBox['title'] : __('Categorii');
                        $directChildren = $onCategory && ! $listsEveryCategory ? $categoryBox['children'] : null;
                        $showDirectChildren = $directChildren !== null && $directChildren->isNotEmpty();
                        $showFilters = ($categoryFilters['hasFilters'] ?? false) === true && ! $showDirectChildren;
                        $showBranch = $onCategory && ($categoryBox['parent'] ?? null) !== null && ! $showDirectChildren;
                    @endphp
                    <div @class(['hidden lg:block' => $showFilters || $showBranch])>
                        <div class="flex">
                        <div class="w-[6px] shrink-0 pt-[70px]">
                            <div class="h-[129px] w-[6px] rounded-l-[5px] bg-[#fe5013]"></div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex h-11 items-center rounded-t-[7px] border border-b-0 border-[#ededed] bg-gradient-to-b from-white to-[#f7f7f7]">
                                <img src="/images/flame.svg" width="16" height="20" class="ml-[12px]" alt="">
                                <span class="ml-[6px] text-sm text-[#ff4e11]">{{ $boxTitle }}</span>
                            </div>
                            @if ($showBranch)
                                <div class="border-x border-hairline">
                                    <x-category-siblings :parent="$categoryBox['parent']" :siblings="$categoryBox['siblings']" :current="$categoryBox['current']" />
                                    @if ($showFilters)
                                        <div class="border-t border-hairline px-[10px] py-3">
                                            <x-category-filters :panel="$categoryFilters" />
                                        </div>
                                    @endif
                                </div>
                            @elseif ($showFilters)
                                <div class="border-x border-hairline px-[10px] py-3">
                                    <x-category-filters :panel="$categoryFilters" />
                                </div>
                            @elseif ($showDirectChildren)
                                <div class="border-x border-hairline pt-[7px]">
                                    @foreach ($directChildren as $child)
                                        <div @class(['flex px-[10px]', 'pb-[7px]' => $loop->last])>
                                            <a href="{{ route('category', $child) }}" class="text-sm font-semibold leading-5 text-menu no-underline hover:text-link">{{ $child->name }}</a>
                                        </div>
                                        @unless ($loop->last)
                                            <div class="dotted-line mx-auto my-[4px] w-[80%] max-w-[180px]"></div>
                                        @endunless
                                    @endforeach
                                </div>
                            @elseif (! $onCategory || $listsEveryCategory)
                                <div class="border-x border-hairline pt-[7px]">
                                    @foreach ($site['productCategories'] as $category)
                                        @php($isOpen = $listsEveryCategory && $category->is($categoryBox['current']))
                                        <div class="flex px-[10px]">
                                            <a
                                                href="{{ route('category', $category) }}"
                                                @class([
                                                    'text-sm leading-5 no-underline hover:text-link',
                                                    'font-bold text-[#444444]' => $isOpen,
                                                    'font-semibold text-menu' => ! $isOpen,
                                                ])
                                            >{{ $category->name }}</a>
                                        </div>
                                        <div class="dotted-line mx-auto my-[4px] w-[80%] max-w-[180px]"></div>
                                        @foreach ($category->children as $child)
                                            @php($isLastChild = $loop->parent->last && $loop->last)
                                            <div @class(['flex px-[10px]', 'pb-[7px]' => $isLastChild])>
                                                <span class="w-[25px] shrink-0"></span>
                                                <a href="{{ route('category', $child) }}" class="text-sm leading-5 text-submenu no-underline hover:text-link hover:underline">{{ $child->name }}</a>
                                            </div>
                                            @unless ($isLastChild)
                                                <div class="dotted-line mx-auto my-[4px] w-[80%] max-w-[180px]"></div>
                                            @endunless
                                        @endforeach
                                    @endforeach
                                </div>
                            @endif
                            <div class="h-[8px] rounded-b-[7px] border border-t-0 border-hairline"></div>
                        </div>
                        </div>
                    </div>
                @endif

                @foreach ($site['serviceCategories'] as $category)
                    <div class="mt-2.5 flex">
                        <div class="w-[6px] shrink-0 pt-[70px]">
                            <div @class(['h-[84px] w-[6px] rounded-l-[5px]', 'bg-[#8c8c8c]' => $site['hasProducts'], 'bg-[#fe5013]' => ! $site['hasProducts']])></div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex h-11 items-center justify-center rounded-t-[7px] border border-b-0 border-[#ededed] bg-gradient-to-b from-white to-[#f7f7f7]">
                                <span class="text-sm text-[#ff4e11]">{{ mb_strtoupper($category->name, 'UTF-8') }}</span>
                            </div>
                            <div class="border-x border-hairline pt-[7px]">
                                @foreach ($category->services as $service)
                                    <div @class(['flex px-[10px]', 'pb-[7px]' => $loop->last])>
                                        <a href="{{ route('service', $service) }}" class="text-sm font-semibold leading-5 text-menu no-underline hover:text-link">{{ $service->name }}</a>
                                    </div>
                                    @unless ($loop->last)
                                        <div class="dotted-line mx-auto my-[4px] w-[80%] max-w-[180px]"></div>
                                    @endunless
                                @endforeach
                            </div>
                            <div class="h-[8px] rounded-b-[7px] border border-t-0 border-hairline"></div>
                        </div>
                    </div>
                @endforeach

                @if (count($site['manufacturers']) && ! isset($categoryFilters))
                    <div class="mt-2.5 flex">
                        <div class="w-[6px] shrink-0 pt-[70px]">
                            <div class="h-[84px] w-[6px] rounded-l-[5px] bg-[#8c8c8c]"></div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex h-11 items-center justify-center rounded-t-[7px] border border-b-0 border-[#ededed] bg-gradient-to-b from-white to-[#f7f7f7]">
                                <span class="text-sm text-[#ff4e11]">{{ __('PRODUCATORI') }}</span>
                            </div>
                            <div class="border-x border-hairline pt-[7px]">
                                @foreach ($site['manufacturers'] as $maker)
                                    <div class="flex px-[10px]">
                                        <span class="text-sm leading-5 text-[#62879f]"><a href="{{ route('manufacturer', $maker['slug']) }}" class="font-semibold text-[#6f6a68] no-underline hover:text-link hover:underline">{{ $maker['name'] }}</a> {{ __('(:total)', ['total' => $maker['total']]) }}</span>
                                    </div>
                                    <div class="dotted-line mx-auto my-[4px] w-[80%] max-w-[180px]"></div>
                                @endforeach
                                <div class="px-[10px] pb-[7px] pt-[5px] text-right">
                                    <a href="{{ route('search') }}" class="text-[#444444] no-underline">{{ __('...Toti producatorii') }}</a>
                                </div>
                            </div>
                            <div class="h-[8px] rounded-b-[7px] border border-t-0 border-hairline"></div>
                        </div>
                    </div>
                @endif
                @endif
            </aside>
            @endunless

            <main @class(['w-full min-w-0', 'order-1 lg:order-2 lg:w-auto lg:flex-1' => ! $wide])>
                {{ $slot }}
            </main>

            @unless ($wide)
            <aside class="order-3 w-full shrink-0 md:w-[calc(50%-5px)] lg:w-56 lg:px-2.5">
                <div class="w-full">
                    <div class="h-[8px] rounded-t-[7px] border border-b-0 border-hairline bg-white"></div>
                    <div class="border-x border-hairline bg-white px-[2px] py-[2px]">
                        <div class="p-[2px] text-center text-sm font-medium text-brand">{{ __('STIRI') }}</div>
                        @foreach ($site['news'] as $item)
                            <div class="p-[5px] text-center text-sm font-semibold text-link">
                                <a href="{{ route('news', $item) }}" class="text-link no-underline hover:underline">{{ $item->title }}</a>
                            </div>
                            <div @class(['px-[5px] text-sm leading-5 text-[#716f6f]', 'pb-[5px]' => $loop->last])>{{ $item->excerpt }}</div>
                        @endforeach
                    </div>
                    <img src="/images/box-bottom-right.svg" width="164" height="33" alt="" class="block h-auto w-full">
                </div>

                @if (count($site['promos']))
                    <div class="mt-2.5 w-full">
                        <div class="h-[8px] rounded-t-[7px] border border-b-0 border-hairline bg-white"></div>
                        <div class="border-x border-hairline bg-white px-[2px] py-[2px]">
                            <div class="p-[2px] text-center text-sm font-medium text-brand">{{ __('SUPER PROMOTIE') }}</div>
                            @foreach ($site['promos'] as $product)
                                <div class="p-[5px] text-center">
                                    <a href="{{ route('product', $product['slug']) }}" class="inline-block">
                                        <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" class="mx-auto block max-w-full">
                                    </a>
                                    <div class="mt-[5px]">
                                        <a href="{{ route('product', $product['slug']) }}" class="text-sm font-semibold text-brand no-underline hover:text-submenu">{{ $product['name'] }}</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <img src="/images/box-bottom-right.svg" width="164" height="33" alt="" class="block h-auto w-full">
                    </div>
                @endif
            </aside>
            @endunless
        </div>

        <x-site-footer />

        <div data-cookies>
            <div class="fixed inset-x-0 bottom-0 z-50 px-3 pb-3" style="display: none" v-show="visible">
                <div class="mx-auto max-w-7xl border border-[#e2e2e2] bg-white p-4 shadow-lg">
                    <p class="m-0 text-base font-medium text-brand">{{ __('Cookie-uri') }}</p>
                    <p class="mt-2 mb-0 leading-[16px] text-[#444444]">
                        {{ __('Folosim doar cookie-uri necesare pentru sesiune, securitatea formularelor și afișarea aleasă în catalog. Nu încărcăm cookie-uri de statistică sau marketing înainte de acordul tău.') }}
                        {{ __('Detalii în') }} <a href="{{ route('cookies') }}" class="text-link">{{ __('Politica de cookie-uri') }}</a>
                        {{ __('și') }} <a href="{{ route('privacy') }}" class="text-link">{{ __('Politica de confidențialitate') }}</a>.
                    </p>
                    <div class="mt-3 border border-hairline p-3 leading-[16px]" style="display: none" v-show="details">
                        <p class="m-0"><strong>{{ __('Necesare') }}</strong> - {{ __('mereu active. Țin sesiunea de autentificare, protecția formularelor și alegerea făcută aici. Nu pot fi oprite.') }}</p>
                        <p class="mt-2 mb-0"><strong>{{ __('Statistici') }}</strong> - {{ __('momentan nu folosim astfel de cookie-uri. Dacă le vom adăuga, vor porni doar după „Acceptă toate”.') }}</p>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button" class="bg-brand px-3 py-1 text-white" @@click="save('all')">{{ __('Acceptă toate') }}</button>
                        <button type="button" class="border border-[#a4a4a4] bg-white px-3 py-1" @@click="save('necessary')">{{ __('Doar necesare') }}</button>
                        <button type="button" class="px-3 py-1 text-link underline" @@click="details = !details">@{{ details ? @json(__('Ascunde detaliile')) : @json(__('Setări')) }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
