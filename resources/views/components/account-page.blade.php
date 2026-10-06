@props(['title', 'current'])

@php
    $crumbs = $current === 'profile'
        ? \App\Support\Breadcrumbs::forPage(__('Contul meu'))
        : \App\Support\Breadcrumbs::forAccount($title);
@endphp

<x-site-layout :title="$title" :breadcrumbs="$crumbs">
    <h1 class="mb-4 text-lg font-medium tracking-tight text-brand">{{ __('Contul meu') }}</h1>
    <nav class="mb-6 flex flex-wrap gap-x-1 border-b border-hairline" aria-label="{{ __('Secțiunile contului') }}">
        @foreach ([
            'profile' => [__('Datele mele'), route('account.home')],
            'addresses' => [__('Adrese'), route('account.addresses')],
            'equipment' => [__('Echipamente'), route('account.equipment')],
            'billing' => [__('Facturare'), route('account.billing')],
            'appointments' => [__('Programări'), route('account.appointments')],
        ] as $key => [$label, $url])
            <a
                href="{{ $url }}"
                @class([
                    '-mb-px border-b-2 px-3 py-2 text-sm no-underline',
                    'border-brand font-medium text-brand' => $current === $key,
                    'border-transparent text-[#444444] hover:text-brand' => $current !== $key,
                ])
                @if ($current === $key) aria-current="page" @endif
            >{{ $label }}</a>
        @endforeach
    </nav>
    @if (session('status'))
        <p class="mb-3 text-link">{{ session('status') }}</p>
    @endif
    @if ($errors->any())
        <div class="mb-3 space-y-1 text-sm text-brand">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
    {{ $slot }}
    <form method="post" action="{{ route('logout') }}">
        @csrf
        <button class="mt-6 underline" type="submit">{{ __('Ieșire') }}</button>
    </form>
</x-site-layout>
