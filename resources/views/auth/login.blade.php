<x-site-layout :title="__('Autentificare')" :breadcrumbs="\App\Support\Breadcrumbs::forPage(__('Autentificare'))" :index="false">
    <h1 class="mb-2 text-lg font-medium tracking-tight text-brand">{{ __('Autentificare') }}</h1>
    @if (session('status'))
        <p class="mb-3 text-link">{{ session('status') }}</p>
    @endif
    <form class="max-w-sm space-y-3" method="post" action="{{ route('login') }}">
        @csrf
        <x-ui.input type="email" name="email" value="{{ old('email') }}" required :placeholder="__('Email')" />
        @error('email')
            <p class="text-sm text-brand">{{ $message }}</p>
        @enderror
        <x-ui.input type="password" name="password" required :placeholder="__('Parola')" />
        <button class="inline-flex h-9 items-center justify-center rounded-lg bg-brand px-3 text-sm font-medium text-white transition-all outline-none hover:bg-brand/90 focus-visible:ring-3 focus-visible:ring-ring/50" type="submit">{{ __('Intra in cont') }}</button>
    </form>
    <p class="mt-4 space-x-3 text-sm">
        <a class="text-link" href="{{ route('register') }}">{{ __('Creează cont') }}</a>
        <a class="text-link" href="{{ route('password.request') }}">{{ __('Am uitat parola') }}</a>
    </p>
    <div class="mt-4 flex flex-wrap gap-3">
        <a class="inline-flex h-9 items-center justify-center rounded-lg border border-input bg-white px-3 text-sm font-medium text-brand" href="{{ route('social.redirect', 'google') }}">{{ __('Continuă cu Google') }}</a>
        <a class="inline-flex h-9 items-center justify-center rounded-lg border border-input bg-white px-3 text-sm font-medium text-brand" href="{{ route('social.redirect', 'facebook') }}">{{ __('Continuă cu Facebook') }}</a>
    </div>
</x-site-layout>
