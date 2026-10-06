<x-site-layout :title="__('Parolă uitată')" :breadcrumbs="\App\Support\Breadcrumbs::forPage(__('Parolă uitată'))">
    <h1 class="mb-2 text-lg font-medium tracking-tight text-brand">{{ __('Parolă uitată') }}</h1>
    @if (session('status'))
        <p class="mb-3 text-link">{{ session('status') }}</p>
    @endif
    <form class="max-w-sm space-y-3" method="post" action="{{ route('password.email') }}">
        @csrf
        <x-ui.input type="email" name="email" value="{{ old('email') }}" required :placeholder="__('Email')" />
        @error('email')
            <p class="text-sm text-brand">{{ $message }}</p>
        @enderror
        <button class="inline-flex h-9 items-center justify-center rounded-lg bg-brand px-3 text-sm font-medium text-white" type="submit">{{ __('Trimite linkul') }}</button>
    </form>
</x-site-layout>
