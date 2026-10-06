@php
    $counties = \App\Support\Counties::options();
    $button = 'inline-flex h-9 items-center justify-center rounded-lg bg-brand px-3 text-sm font-medium text-white';
@endphp

<x-account-page :title="__('Adrese')" current="addresses">
    @foreach ($user->addresses as $address)
        <form class="mt-3 max-w-sm space-y-3 border border-hairline p-3" method="post" action="{{ route('account.addresses.update', $address) }}">
            @csrf
            <x-ui.input type="text" name="label" value="{{ $address->label }}" :placeholder="__('Etichetă, de exemplu Acasă')" />
            <x-ui.select name="county" required>
                @foreach ($counties as $county)
                    <option value="{{ $county }}" @selected($address->county === $county)>{{ $county }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.input type="text" name="city" value="{{ $address->city }}" required :placeholder="__('Localitate')" />
            <x-ui.input type="text" name="street" value="{{ $address->street }}" required :placeholder="__('Stradă')" />
            <x-ui.input type="text" name="street_number" value="{{ $address->street_number }}" required :placeholder="__('Număr')" />
            <x-ui.input type="text" name="postal_code" value="{{ $address->postal_code }}" :placeholder="__('Cod poștal')" />
            <x-ui.input type="text" name="details" value="{{ $address->details }}" :placeholder="__('Bloc, scară, apartament')" />
            <button class="{{ $button }}" type="submit">{{ __('Salvează adresa') }}</button>
        </form>
        <form method="post" action="{{ route('account.addresses.delete', $address) }}">
            @csrf
            @method('DELETE')
            <button class="mt-1 text-sm underline" type="submit">{{ __('Șterge adresa') }}</button>
        </form>
    @endforeach
    <form class="mt-3 max-w-sm space-y-3" method="post" action="{{ route('account.addresses.store') }}">
        @csrf
        <x-ui.input type="text" name="label" value="{{ old('label') }}" :placeholder="__('Etichetă, de exemplu Acasă')" />
        <x-ui.select name="county" required>
            <option value="">{{ __('Județ') }}</option>
            @foreach ($counties as $county)
                <option value="{{ $county }}" @selected(old('county') === $county)>{{ $county }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.input type="text" name="city" value="{{ old('city') }}" required :placeholder="__('Localitate')" />
        <x-ui.input type="text" name="street" value="{{ old('street') }}" required :placeholder="__('Stradă')" />
        <x-ui.input type="text" name="street_number" value="{{ old('street_number') }}" required :placeholder="__('Număr')" />
        <x-ui.input type="text" name="postal_code" value="{{ old('postal_code') }}" :placeholder="__('Cod poștal')" />
        <x-ui.input type="text" name="details" value="{{ old('details') }}" :placeholder="__('Bloc, scară, apartament')" />
        <button class="{{ $button }}" type="submit">{{ __('Adaugă adresa') }}</button>
    </form>
</x-account-page>
