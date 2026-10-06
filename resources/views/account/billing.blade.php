@php
    $counties = \App\Support\Counties::options();
    $button = 'inline-flex h-9 items-center justify-center rounded-lg bg-brand px-3 text-sm font-medium text-white';
@endphp

<x-account-page :title="__('Facturare')" current="billing">
    @foreach ($user->billingProfiles as $profile)
        <div class="mt-3 max-w-sm border border-hairline p-3 text-sm">
            <p class="font-medium">{{ $profile->displayName() }}</p>
            <p>{{ $profile->type === 'company' ? __('Persoană juridică') : __('Persoană fizică') }}</p>
            <p>{{ $profile->line() }}</p>
            @if ($profile->type === 'company')
                <p>{{ __('CUI') }}: {{ $profile->cui }}</p>
            @endif
        </div>
        <form method="post" action="{{ route('account.billing.delete', $profile) }}">
            @csrf
            @method('DELETE')
            <button class="mt-1 text-sm underline" type="submit">{{ __('Șterge profilul') }}</button>
        </form>
    @endforeach
    <form class="mt-3 max-w-sm space-y-3" method="post" action="{{ route('account.billing.store') }}">
        @csrf
        <x-ui.select name="type" required>
            <option value="individual">{{ __('Persoană fizică') }}</option>
            <option value="company">{{ __('Persoană juridică') }}</option>
        </x-ui.select>
        <x-ui.input type="text" name="last_name" :placeholder="__('Nume')" />
        <x-ui.input type="text" name="first_name" :placeholder="__('Prenume')" />
        <x-ui.input type="text" name="company_name" :placeholder="__('Denumire firmă')" />
        <x-ui.input type="text" name="cui" :placeholder="__('CUI')" />
        <x-ui.input type="text" name="registration_number" :placeholder="__('Nr. Reg. Comerțului')" />
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_vat_payer" value="1">
            {{ __('Plătitor de TVA') }}
        </label>
        @if ($user->addresses->isNotEmpty())
            <x-ui.select name="copy_address_id">
                <option value="">{{ __('Copiază o adresă de service') }}</option>
                @foreach ($user->addresses as $address)
                    <option value="{{ $address->id }}">{{ $address->line() }}</option>
                @endforeach
            </x-ui.select>
        @endif
        <x-ui.select name="county">
            <option value="">{{ __('Județ') }}</option>
            @foreach ($counties as $county)
                <option value="{{ $county }}">{{ $county }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.input type="text" name="city" :placeholder="__('Localitate')" />
        <x-ui.input type="text" name="street" :placeholder="__('Stradă')" />
        <x-ui.input type="text" name="street_number" :placeholder="__('Număr')" />
        <x-ui.input type="text" name="postal_code" :placeholder="__('Cod poștal')" />
        <x-ui.input type="text" name="details" :placeholder="__('Bloc, scară, apartament')" />
        <x-ui.input type="text" name="bank" :placeholder="__('Bancă')" />
        <x-ui.input type="text" name="iban" :placeholder="__('IBAN')" />
        <button class="{{ $button }}" type="submit">{{ __('Adaugă facturarea') }}</button>
    </form>
</x-account-page>
