<x-site-layout :title="__('Cont nou')" :breadcrumbs="\App\Support\Breadcrumbs::forPage(__('Cont nou'))" :index="false">
    <h1 class="mb-2 text-lg font-medium tracking-tight text-brand">{{ __('Cont nou') }}</h1>
    <form class="max-w-sm space-y-3" method="post" action="{{ route('register') }}">
        @csrf
        <x-ui.input type="text" name="name" value="{{ old('name') }}" required :placeholder="__('Nume')" />
        @error('name')
            <p class="text-sm text-brand">{{ $message }}</p>
        @enderror
        <x-ui.input type="email" name="email" value="{{ old('email') }}" required :placeholder="__('Email')" />
        @error('email')
            <p class="text-sm text-brand">{{ $message }}</p>
        @enderror
        <x-ui.input type="text" name="phone" value="{{ old('phone') }}" :placeholder="__('Telefon')" />
        @error('phone')
            <p class="text-sm text-brand">{{ $message }}</p>
        @enderror
        <x-ui.input type="password" name="password" required :placeholder="__('Parola')" />
        @error('password')
            <p class="text-sm text-brand">{{ $message }}</p>
        @enderror
        <x-ui.input type="password" name="password_confirmation" required :placeholder="__('Confirma parola')" />
        <button class="inline-flex h-9 items-center justify-center rounded-lg bg-brand px-3 text-sm font-medium text-white transition-all outline-none hover:bg-brand/90 focus-visible:ring-3 focus-visible:ring-ring/50" type="submit">{{ __('Creeaza cont') }}</button>
    </form>
    <p class="mt-4 space-x-3 text-sm">
        <a class="text-link" href="{{ route('login') }}">{{ __('Autentificare') }}</a>
    </p>
    <div class="mt-4 flex flex-wrap gap-3">
        <a class="inline-flex h-9 items-center justify-center rounded-lg border border-input bg-white px-3 text-sm font-medium text-brand" href="{{ route('social.redirect', 'google') }}">{{ __('Continuă cu Google') }}</a>
        <a class="inline-flex h-9 items-center justify-center rounded-lg border border-input bg-white px-3 text-sm font-medium text-brand" href="{{ route('social.redirect', 'facebook') }}">{{ __('Continuă cu Facebook') }}</a>
    </div>
</x-site-layout>
