@props(['title', 'current'])

@php
    $crumbs = $current === 'profile'
        ? \App\Support\Breadcrumbs::forPage(__('Contul meu'))
        : \App\Support\Breadcrumbs::forAccount($title);
@endphp

<x-site-layout :title="$title" :breadcrumbs="$crumbs" :account="true" :account-current="$current" :index="false">
    <h1 class="mb-4 text-lg font-medium tracking-tight text-[#222222]">{{ $title }}</h1>
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
