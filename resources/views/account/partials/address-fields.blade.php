@php
    $formKey = $address ? (string) $address->id : 'new';
    $useOld = old('address_form') === $formKey;
    $field = function (string $name) use ($address, $useOld) {
        $stored = $address?->{$name} ?? '';

        return $useOld ? old($name, $stored) : $stored;
    };
@endphp

<label class="block text-sm">
    <span class="mb-1 block">{{ __('Etichetă') }}</span>
    <x-ui.input type="text" name="label" value="{{ $field('label') }}" />
</label>
<label class="block text-sm">
    <span class="mb-1 block">{{ __('Județ') }}</span>
    <x-ui.select name="county" required>
        <option value="">{{ __('Alege județul') }}</option>
        @foreach ($counties as $county)
            <option value="{{ $county }}" @selected($field('county') === $county)>{{ $county }}</option>
        @endforeach
    </x-ui.select>
</label>
<label class="block text-sm">
    <span class="mb-1 block">{{ __('Localitate') }}</span>
    <x-ui.input type="text" name="city" value="{{ $field('city') }}" required />
</label>
<label class="block text-sm">
    <span class="mb-1 block">{{ __('Stradă') }}</span>
    <x-ui.input type="text" name="street" value="{{ $field('street') }}" required />
</label>
<label class="block text-sm">
    <span class="mb-1 block">{{ __('Număr') }}</span>
    <x-ui.input type="text" name="street_number" value="{{ $field('street_number') }}" required />
</label>
<label class="block text-sm">
    <span class="mb-1 block">{{ __('Cod poștal') }}</span>
    <x-ui.input type="text" name="postal_code" value="{{ $field('postal_code') }}" />
</label>
<label class="block text-sm">
    <span class="mb-1 block">{{ __('Detalii') }}</span>
    <x-ui.input type="text" name="details" value="{{ $field('details') }}" />
</label>
