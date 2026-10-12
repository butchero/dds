@php
    $button = 'inline-flex h-9 items-center justify-center rounded-lg bg-brand px-3 text-sm font-medium text-white';
@endphp

<x-account-page :title="__('Datele mele')" current="profile">
    <form class="max-w-sm space-y-3" method="post" action="{{ route('account.profile') }}">
        @csrf
        <label class="block text-sm">
            <span class="mb-1 block">{{ __('Nume') }}</span>
            <x-ui.input type="text" name="name" value="{{ old('name', $user->name) }}" required />
        </label>
        <label class="block text-sm">
            <span class="mb-1 block">{{ __('E-mail') }}</span>
            <x-ui.input type="email" name="email" value="{{ old('email', $user->email) }}" required />
        </label>
        <button class="{{ $button }}" type="submit">{{ __('Salvează datele') }}</button>
    </form>
    <form class="mt-6 max-w-sm space-y-3" method="post" action="{{ route('account.phone') }}">
        @csrf
        <label class="block text-sm">
            <span class="mb-1 block">{{ __('Telefon') }}</span>
            <x-ui.input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required />
        </label>
        <button class="{{ $button }}" type="submit">{{ __('Confirmă telefonul') }}</button>
    </form>
    <form class="mt-6 max-w-sm space-y-3" method="post" action="{{ route('account.password') }}">
        @csrf
        @if (filled($user->password))
            <label class="block text-sm">
                <span class="mb-1 block">{{ __('Parola actuală') }}</span>
                <x-ui.input type="password" name="current_password" required />
            </label>
        @endif
        <label class="block text-sm">
            <span class="mb-1 block">{{ __('Parolă nouă') }}</span>
            <x-ui.input type="password" name="password" required />
        </label>
        <label class="block text-sm">
            <span class="mb-1 block">{{ __('Confirmă parola') }}</span>
            <x-ui.input type="password" name="password_confirmation" required />
        </label>
        <button class="{{ $button }}" type="submit">{{ __('Schimbă parola') }}</button>
    </form>
</x-account-page>
