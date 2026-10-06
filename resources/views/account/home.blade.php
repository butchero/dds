@php
    $button = 'inline-flex h-9 items-center justify-center rounded-lg bg-brand px-3 text-sm font-medium text-white';
@endphp

<x-account-page :title="__('Datele mele')" current="profile">
    <form class="max-w-sm space-y-3" method="post" action="{{ route('account.profile') }}">
        @csrf
        <x-ui.input type="text" name="name" value="{{ old('name', $user->name) }}" required :placeholder="__('Nume')" />
        <x-ui.input type="email" name="email" value="{{ old('email', $user->email) }}" required :placeholder="__('Email')" />
        <button class="{{ $button }}" type="submit">{{ __('Salvează datele') }}</button>
    </form>
    <form class="mt-4 max-w-sm space-y-3" method="post" action="{{ route('account.phone') }}">
        @csrf
        <x-ui.input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required :placeholder="__('Telefon')" />
        <button class="{{ $button }}" type="submit">{{ __('Confirmă telefonul') }}</button>
    </form>
    <form class="mt-4 max-w-sm space-y-3" method="post" action="{{ route('account.password') }}">
        @csrf
        @if (filled($user->password))
            <x-ui.input type="password" name="current_password" required :placeholder="__('Parola actuală')" />
        @endif
        <x-ui.input type="password" name="password" required :placeholder="__('Parolă nouă')" />
        <x-ui.input type="password" name="password_confirmation" required :placeholder="__('Confirma parola')" />
        <button class="{{ $button }}" type="submit">{{ __('Schimbă parola') }}</button>
    </form>
</x-account-page>
