<x-site-layout
    wide
    :title="__('Contact')"
    :description="__('Adresa, telefoanele și formularul de contact :brand din :city.', ['brand' => \App\Models\SiteSetting::get('title_suffix'), 'city' => $office['city']])"
    :breadcrumbs="$breadcrumbs"
>
    <div class="bg-brand px-[30px] py-4 text-white">
        <h1 class="text-lg font-bold uppercase tracking-wide">{{ __('Contact') }}</h1>
    </div>

    <div class="mt-[5px] grid items-stretch gap-[10px] lg:grid-cols-2">
        <div @class(['relative overflow-hidden min-h-[420px] lg:min-h-[469px]' => filled($office['mapEmbed'])])>
            @if (filled($office['mapEmbed']))
                <iframe class="absolute -top-36 left-0 h-[calc(100%+18rem)] w-full grayscale" src="{{ $office['mapEmbed'] }}" title="{{ __('Harta sediului :brand', ['brand' => \App\Models\SiteSetting::get('title_suffix')]) }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            @endif
            <div @class([
                'border-l-[10px] border-brand bg-white sm:flex',
                'absolute bottom-[5%] left-[5%] z-10 w-[90%]' => filled($office['mapEmbed']),
            ])>
                <div class="p-5 sm:w-1/2 sm:border-r sm:border-[#ebebeb]">
                    <h2 class="mb-2.5 text-xs uppercase text-brand">{{ __('Localizare') }}</h2>
                    @if (filled($office['name']))
                        <p class="text-xs leading-[18px] text-[#8e8e8e]">{{ $office['name'] }}</p>
                    @endif
                    @if (filled($office['street']) || filled($office['city']))
                        <p class="text-xs leading-[18px] text-[#8e8e8e]">{{ $office['street'] }}@if (filled($office['street']) && filled($office['city'])), @endif{{ $office['city'] }}</p>
                    @endif
                </div>
                <div class="border-t border-[#ebebeb] p-5 sm:w-1/2 sm:border-t-0">
                    <h2 class="mb-2.5 text-xs uppercase text-brand">{{ __('Informații de contact') }}</h2>
                    @if ($office['phone'])
                        <p class="text-xs leading-[18px] text-[#8e8e8e]">
                            <strong>{{ __('Telefon') }}:</strong>
                            @if ($office['phone']['tel'])
                                <a class="text-[#8e8e8e] hover:underline" href="tel:{{ $office['phone']['tel'] }}">{{ $office['phone']['display'] }}</a>
                            @else
                                {{ $office['phone']['display'] }}
                            @endif
                        </p>
                    @endif
                    @if (filled($office['fax']))
                        <p class="text-xs leading-[18px] text-[#8e8e8e]"><strong>{{ __('Fax') }}:</strong> {{ $office['fax'] }}</p>
                    @endif
                    @if ($office['mobiles'] !== [])
                        <p class="text-xs leading-[18px] text-[#8e8e8e]">
                            <strong>{{ __('Mobil') }}:</strong>
                            @foreach ($office['mobiles'] as $mobile)
                                @if ($mobile['tel'])
                                    <a class="text-[#8e8e8e] hover:underline" href="tel:{{ $mobile['tel'] }}">{{ $mobile['display'] }}</a>
                                @else
                                    {{ $mobile['display'] }}
                                @endif
                                @if (! $loop->last), @endif
                            @endforeach
                        </p>
                    @endif
                    @if (filled($office['email']))
                        <p class="text-xs leading-[18px] text-[#8e8e8e]">
                            <strong>{{ __('E-mail') }}:</strong>
                            <a class="text-[#8e8e8e] hover:underline" href="mailto:{{ $office['email'] }}">{{ $office['email'] }}</a>
                        </p>
                    @endif
                    @if (filled($office['mapLink']))
                        <p class="mt-2 text-xs leading-[18px]">
                            <a class="text-link" href="{{ $office['mapLink'] }}" target="_blank" rel="noopener">{{ __('Vizualizare hartă mărită') }}</a>
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white">
            <div class="border-b border-[#f3f3f3] px-[30px] py-[30px]">
                <h2 class="text-[22px] text-brand">{{ __('Formular de contact') }}</h2>
            </div>
            <form class="px-[30px] py-[30px]" method="post" action="{{ route('contact.store') }}">
                @csrf
                <div class="sr-only" inert>
                    <label>
                        {{ __('Site') }}
                        <input type="text" name="company_url" tabindex="-1" autocomplete="off">
                    </label>
                </div>
                @if (session('status'))
                    <p class="mb-5 text-center text-lg text-brand">{{ session('status') }}</p>
                @endif
                <div class="grid gap-5 sm:grid-cols-3">
                    <label class="block">
                        <span class="mb-2.5 block text-[11px] uppercase text-[#bcbcbc]">{{ __('Nume') }}</span>
                        <input
                            @class(['h-[39px] w-full bg-[#f3f3f3] px-[15px] text-sm text-[#8e8e8e] outline-none', 'border border-brand' => $errors->has('name'), 'border-0' => ! $errors->has('name')])
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            maxlength="120"
                            autocomplete="name"
                            placeholder="{{ __('Nume') }}"
                        >
                    </label>
                    <label class="block">
                        <span class="mb-2.5 block text-[11px] uppercase text-[#bcbcbc]">{{ __('E-mail') }}</span>
                        <input
                            @class(['h-[39px] w-full bg-[#f3f3f3] px-[15px] text-sm text-[#8e8e8e] outline-none', 'border border-brand' => $errors->has('email'), 'border-0' => ! $errors->has('email')])
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            maxlength="255"
                            autocomplete="email"
                            placeholder="{{ __('E-mail') }}"
                        >
                    </label>
                    <label class="block">
                        <span class="mb-2.5 block text-[11px] uppercase text-[#bcbcbc]">{{ __('Telefon') }}</span>
                        <input
                            @class(['h-[39px] w-full bg-[#f3f3f3] px-[15px] text-sm text-[#8e8e8e] outline-none', 'border border-brand' => $errors->has('phone'), 'border-0' => ! $errors->has('phone')])
                            type="tel"
                            name="phone"
                            value="{{ old('phone') }}"
                            required
                            maxlength="40"
                            autocomplete="tel"
                            placeholder="{{ __('Telefon') }}"
                        >
                    </label>
                </div>
                @error('name')
                    <p class="mt-1 text-[11px] text-brand">{{ $message }}</p>
                @enderror
                @error('email')
                    <p class="mt-1 text-[11px] text-brand">{{ $message }}</p>
                @enderror
                @error('phone')
                    <p class="mt-1 text-[11px] text-brand">{{ $message }}</p>
                @enderror

                <label class="mt-5 block">
                    <span class="mb-2.5 block text-[11px] uppercase text-[#bcbcbc]">{{ __('Departament') }}</span>
                    <select
                        @class(['h-[39px] w-full bg-[#f3f3f3] px-[15px] text-sm text-[#8e8e8e] outline-none', 'border border-brand' => $errors->has('department'), 'border-0' => ! $errors->has('department')])
                        name="department"
                        required
                    >
                        <option value="">{{ __('Alege departamentul') }}</option>
                        @foreach ($departments as $value => $label)
                            <option value="{{ $value }}" @selected(old('department') === $value)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </label>
                @error('department')
                    <p class="mt-1 text-[11px] text-brand">{{ $message }}</p>
                @enderror

                <label class="mt-5 block">
                    <span class="mb-2.5 block text-[11px] uppercase text-[#bcbcbc]">{{ __('Mesaj') }}</span>
                    <textarea
                        @class(['min-h-[165px] w-full bg-[#f3f3f3] p-[15px] text-sm text-[#8e8e8e] outline-none', 'border border-brand' => $errors->has('message'), 'border-0' => ! $errors->has('message')])
                        name="message"
                        required
                        maxlength="5000"
                        placeholder="{{ __('Mesaj') }}"
                    >{{ old('message') }}</textarea>
                </label>
                @error('message')
                    <p class="mt-1 text-[11px] text-brand">{{ $message }}</p>
                @enderror

                <label class="mt-5 block">
                    <span class="mb-2.5 block text-[11px] uppercase text-[#bcbcbc]">{{ __('Cod de verificare') }}</span>
                    <span class="flex flex-wrap items-center gap-3">
                        <input
                            @class(['h-[39px] min-w-40 flex-1 bg-[#f3f3f3] px-[15px] text-sm text-[#8e8e8e] outline-none', 'border border-brand' => $errors->has('verification_code'), 'border-0' => ! $errors->has('verification_code')])
                            type="text"
                            name="verification_code"
                            value="{{ old('verification_code') }}"
                            required
                            maxlength="10"
                            autocomplete="off"
                            spellcheck="false"
                            aria-describedby="contact-challenge"
                        >
                        <img id="contact-challenge" class="border border-[#f3f3f3] bg-[#f7f7f7]" src="{{ route('contact.challenge') }}" width="160" height="48" alt="{{ __('Cod de verificare') }}">
                    </span>
                </label>
                @error('verification_code')
                    <p class="mt-1 text-[11px] text-brand">{{ $message }}</p>
                @enderror

                <div class="mt-5 grid grid-cols-1 gap-2.5 sm:grid-cols-[4fr_1fr]">
                    <button class="h-10 bg-brand text-sm uppercase text-white" type="submit">{{ __('Trimite mesaj') }}</button>
                    <button class="h-10 bg-[#8e8e8e] text-sm uppercase text-white" type="reset">{{ __('Reset') }}</button>
                </div>
            </form>
        </div>
    </div>
    <script type="application/ld+json">
        @json($office['schema'])
    </script>
</x-site-layout>
