<x-site-layout :title="__('Parolă nouă')" :breadcrumbs="\App\Support\Breadcrumbs::forPage(__('Parolă nouă'))" :index="false">
    <h1 class="mb-2 text-lg font-medium tracking-tight text-brand">{{ __('Parolă nouă') }}</h1>
    <form class="max-w-sm space-y-3" method="post" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-ui.input type="email" name="email" value="{{ old('email', $email) }}" required :placeholder="__('Email')" />
        @error('email')
            <p class="text-sm text-brand">{{ $message }}</p>
        @enderror
        <x-ui.input type="password" name="password" required :placeholder="__('Parola')" />
        @error('password')
            <p class="text-sm text-brand">{{ $message }}</p>
        @enderror
        <x-ui.input type="password" name="password_confirmation" required :placeholder="__('Confirma parola')" />
        <button class="inline-flex h-9 items-center justify-center rounded-lg bg-brand px-3 text-sm font-medium text-white" type="submit">{{ __('Salvează parola') }}</button>
    </form>
</x-site-layout>
