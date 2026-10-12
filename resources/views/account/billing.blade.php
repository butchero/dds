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
        <label class="block text-sm">
            <span class="mb-1 block">{{ __('Tip') }}</span>
            <x-ui.select name="type" required>
                <option value="individual">{{ __('Persoană fizică') }}</option>
                <option value="company">{{ __('Persoană juridică') }}</option>
            </x-ui.select>
        </label>
        <label class="block text-sm">
            <span class="mb-1 block">{{ __('Nume') }}</span>
            <x-ui.input type="text" name="last_name" />
        </label>
        <label class="block text-sm">
            <span class="mb-1 block">{{ __('Prenume') }}</span>
            <x-ui.input type="text" name="first_name" />
        </label>
        <label class="block text-sm">
            <span class="mb-1 block">{{ __('Denumire firmă') }}</span>
            <x-ui.input type="text" name="company_name" />
        </label>
        <label class="block text-sm">
            <span class="mb-1 block">{{ __('CUI') }}</span>
            <x-ui.input type="text" name="cui" />
        </label>
        <label class="block text-sm">
            <span class="mb-1 block">{{ __('Număr de înregistrare') }}</span>
            <x-ui.input type="text" name="registration_number" />
        </label>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_vat_payer" value="1">
            {{ __('Plătitor de TVA') }}
        </label>
        @if ($user->addresses->isNotEmpty())
            <label class="block text-sm">
                <span class="mb-1 block">{{ __('Copiază o adresă de service') }}</span>
                <x-ui.select name="copy_address_id">
                    <option value="">{{ __('Nu copia') }}</option>
                    @foreach ($user->addresses as $address)
                        <option value="{{ $address->id }}">{{ $address->cardLine() }}</option>
                    @endforeach
                </x-ui.select>
            </label>
        @endif
        <label class="block text-sm">
            <span class="mb-1 block">{{ __('Județ') }}</span>
            <x-ui.select name="county">
                <option value="">{{ __('Alege județul') }}</option>
                @foreach ($counties as $county)
                    <option value="{{ $county }}">{{ $county }}</option>
                @endforeach
            </x-ui.select>
        </label>
        <label class="block text-sm">
            <span class="mb-1 block">{{ __('Localitate') }}</span>
            <x-ui.input type="text" name="city" />
        </label>
        <label class="block text-sm">
            <span class="mb-1 block">{{ __('Stradă') }}</span>
            <x-ui.input type="text" name="street" />
        </label>
        <label class="block text-sm">
            <span class="mb-1 block">{{ __('Număr') }}</span>
            <x-ui.input type="text" name="street_number" />
        </label>
        <label class="block text-sm">
            <span class="mb-1 block">{{ __('Cod poștal') }}</span>
            <x-ui.input type="text" name="postal_code" />
        </label>
        <label class="block text-sm">
            <span class="mb-1 block">{{ __('Detalii') }}</span>
            <x-ui.input type="text" name="details" />
        </label>
        <label class="block text-sm">
            <span class="mb-1 block">{{ __('Bancă') }}</span>
            <x-ui.input type="text" name="bank" />
        </label>
        <label class="block text-sm">
            <span class="mb-1 block">{{ __('IBAN') }}</span>
            <x-ui.input type="text" name="iban" />
        </label>
        <button class="{{ $button }}" type="submit">{{ __('Adaugă facturarea') }}</button>
    </form>
</x-account-page>
