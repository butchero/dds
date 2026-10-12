@php
    $office = \App\Support\ContactOffice::present();
    $publicName = trim((string) \App\Models\SiteSetting::get('title_suffix'));
    $copyrightStart = trim((string) \App\Models\SiteSetting::get('copyright_start_year'));
    $about = trim((string) \App\Models\SiteSetting::get('meta_description'));
    $place = collect([$publicName !== '' ? $publicName : $office['name'], $office['city']])->filter()->implode(', ');
    $mobiles = collect($office['mobiles'])->map(function (array $mobile): string {
        $label = e($mobile['display']);

        if ($mobile['tel']) {
            return '<a class="text-link hover:underline" href="tel:'.e($mobile['tel']).'">'.$label.'</a>';
        }

        return $label;
    })->implode(', ');
    $links = [
        [__('Centrale termice'), '/categorie/centrale-termice'],
        [__('Producatori'), route('search')],
        [__('Intrebari Frecvente'), '/servicii/contact'],
        [__('Despre Noi'), '/servicii/despre-noi'],
        [__('Termeni si conditii'), '/servicii/despre-noi'],
        [__('Sitemap'), route('home')],
        [__('Contact'), route('contact')],
    ];
@endphp

<footer class="mt-6">
    <div class="overflow-hidden rounded-[7px] border border-[#e2e2e2] bg-white">
        <div class="h-[3px] bg-brand"></div>
        <div class="grid lg:grid-cols-3">
            <section class="border-b border-hairline p-5 sm:p-6 lg:border-r lg:border-b-0">
                <h2 class="text-base font-medium text-brand">{{ __('Contactează-ne') }}</h2>
                <div class="mt-3 text-sm leading-6">
                    @if (filled($office['name']))
                        <p class="font-medium text-[#444444]">{{ $office['name'] }}</p>
                    @endif
                    @if (filled($office['street']) || filled($office['city']))
                        <p class="text-[#716f6f]">{{ $office['street'] }}@if (filled($office['street']) && filled($office['city'])), @endif{{ $office['city'] }}</p>
                    @endif
                    <div class="mt-3 flex flex-col gap-1 text-[#716f6f]">
                        @if ($office['phone'])
                            <p>
                                <span class="font-medium text-[#444444]">{{ __('Telefon') }}:</span>
                                @if ($office['phone']['tel'])
                                    <a class="text-link hover:underline" href="tel:{{ $office['phone']['tel'] }}">{{ $office['phone']['display'] }}</a>
                                @else
                                    {{ $office['phone']['display'] }}
                                @endif
                            </p>
                        @endif
                        @if ($mobiles !== '')
                            <p>
                                <span class="font-medium text-[#444444]">{{ __('Mobil') }}:</span>
                                {!! $mobiles !!}
                            </p>
                        @endif
                        @if (filled($office['fax']))
                            <p><span class="font-medium text-[#444444]">{{ __('Fax') }}:</span> {{ $office['fax'] }}</p>
                        @endif
                        @if (filled($office['email']))
                            <p>
                                <span class="font-medium text-[#444444]">{{ __('E-mail') }}:</span>
                                <a class="text-link hover:underline" href="mailto:{{ $office['email'] }}">{{ $office['email'] }}</a>
                            </p>
                        @endif
                    </div>
                </div>
            </section>

            <section class="border-b border-hairline p-5 sm:p-6 lg:border-r lg:border-b-0">
                <h2 class="text-base font-medium text-brand">{{ __('Caută') }}</h2>
                <form class="mt-3 flex items-center gap-2" action="{{ route('search') }}" method="get">
                    <span class="shrink-0 text-sm font-semibold tracking-wide text-brand">{{ __('Cauta:') }}</span>
                    <div class="min-w-0 flex-1">
                        <x-ui.input type="search" name="s" maxlength="30" aria-label="{{ __('Cauta in site') }}" class="h-8!" />
                    </div>
                    <button type="submit" aria-label="{{ __('Cauta') }}" class="inline-flex size-8 shrink-0 items-center justify-center rounded-md border border-input bg-white text-[#444444] shadow-xs transition-all outline-none hover:bg-muted focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50">
                        <svg class="h-[13px] w-[13px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>
                    </button>
                </form>
            </section>

            <section class="border-b border-hairline p-5 sm:p-6 lg:border-b-0">
                <h2 class="text-base font-medium text-brand">{{ __('Despre noi') }}</h2>
                @if ($about !== '')
                    <p class="mt-3 text-sm leading-6 text-[#716f6f]">{{ $about }}</p>
                @endif
                <a href="/servicii/despre-noi" class="mt-3 inline-block text-sm text-link hover:underline">{{ __('Citește mai mult') }}</a>
            </section>
        </div>

        <nav class="border-b border-hairline px-5 py-4 sm:px-6" aria-label="{{ __('Informații') }}">
            <ul class="grid grid-cols-2 gap-x-6 gap-y-1.5 text-sm sm:grid-cols-4">
                @foreach ($links as [$label, $url])
                    <li>
                        <a href="{{ $url }}" class="text-brand no-underline hover:text-submenu hover:underline">{{ $label }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="px-5 py-4 sm:px-6">
            <div class="flex flex-col gap-1 text-sm text-[#716f6f] sm:flex-row sm:items-baseline sm:justify-between">
                <p>© {{ $copyrightStart }}-{{ now()->year }}@if ($place !== '') - {{ $place }}@endif</p>
                <p>{{ __('Centrale termice, aer condiționat și panouri solare') }}</p>
            </div>
            <div class="mt-3 flex flex-col gap-1 text-center text-sm leading-6 text-[#716f6f]">
                <p>
                    {{ __('Citeste') }} <a href="{{ route('privacy') }}" class="text-link">{{ __('Politica de Confidentialitate') }}</a>
                    {{ __('si continuarea navigarii pe acest site implica acceptarea') }}
                    <a href="{{ route('cookies') }}" class="text-link">{{ __('Politicii de Cookies') }}</a>.
                    <button type="button" class="text-link underline" data-cookies-open>{{ __('Setări cookie-uri') }}</button>
                </p>
                <p>
                    <a href="https://www.cazare-pe-litoral.ro" title="{{ __('Cazare pe litoral') }}" class="text-brand no-underline hover:text-submenu hover:underline">{{ __('Cazare pe litoral') }}</a>
                    <span aria-hidden="true"> · </span>
                    <a href="https://www.magazin-blanuri.ro" title="{{ __('Magazin blanuri - haine din blana') }}" class="text-brand no-underline hover:text-submenu hover:underline">{{ __('Magazin blanuri') }}</a>
                    <span aria-hidden="true"> · </span>
                    {{ __('un produs proaspat oferit de') }} <a href="https://www.layerzero.ro" title="{{ __('Web Design') }}" class="text-link">layerzero.ro</a>
                </p>
            </div>
        </div>
    </div>
</footer>
