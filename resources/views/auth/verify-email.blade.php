<x-site-layout :title="__('Confirmă emailul')" :breadcrumbs="\App\Support\Breadcrumbs::forPage(__('Confirmă emailul'))">
    <h1 class="mb-2 text-lg font-medium tracking-tight text-brand">{{ __('Confirmă emailul') }}</h1>
    @if (session('status'))
        <p class="mb-3 text-link">{{ session('status') }}</p>
    @endif
    <p class="mb-3 max-w-md text-sm">{{ __('Ți-am trimis un link la :email. Deschide-l ca să intri în cont.', ['email' => auth()->user()->email]) }}</p>
    <form method="post" action="{{ route('verification.send') }}">
        @csrf
        <button class="inline-flex h-9 items-center justify-center rounded-lg bg-brand px-3 text-sm font-medium text-white" type="submit">{{ __('Trimite din nou') }}</button>
    </form>
    <form class="mt-4" method="post" action="{{ route('logout') }}">
        @csrf
        <button class="underline" type="submit">{{ __('Ieșire') }}</button>
    </form>
</x-site-layout>
