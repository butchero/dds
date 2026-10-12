@php
    $formKey = $item ? (string) $item->id : 'new';
    $useOld = old('equipment_form') === $formKey;
    $field = function (string $name) use ($item, $useOld) {
        $stored = $item?->{$name} ?? '';

        return $useOld ? old($name, $stored) : $stored;
    };
@endphp

<label class="block text-sm">
    <span class="mb-1 block">{{ __('Nume') }}</span>
    <x-ui.input type="text" name="name" value="{{ $field('name') }}" required />
</label>
<label class="block text-sm">
    <span class="mb-1 block">{{ __('Tip') }}</span>
    <x-ui.input type="text" name="type" value="{{ $field('type') }}" />
</label>
<label class="block text-sm">
    <span class="mb-1 block">{{ __('Adresă') }}</span>
    <x-ui.select name="address_id" required>
        <option value="">{{ __('Alege adresa') }}</option>
        @foreach ($user->addresses as $address)
            <option value="{{ $address->id }}" @selected((string) $field('address_id') === (string) $address->id)>{{ $address->cardLine() }}</option>
        @endforeach
    </x-ui.select>
</label>
<label class="block text-sm">
    <span class="mb-1 block">{{ __('Interval') }}</span>
    <x-ui.select name="interval_months" required>
        <option value="12" @selected((string) ($field('interval_months') ?: 24) === '12')>{{ __('12 luni') }}</option>
        <option value="24" @selected((string) ($field('interval_months') ?: 24) === '24')>{{ __('24 luni') }}</option>
    </x-ui.select>
</label>
<label class="block text-sm">
    <span class="mb-1 block">{{ __('Data ultimei revizii') }}</span>
    <x-ui.input type="date" name="last_revision_on" value="{{ $item && ! $useOld ? $item->last_revision_on?->toDateString() : old('last_revision_on') }}" />
</label>
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="notify_email" value="1" @checked($useOld ? old('notify_email') : ($item?->notify_email ?? false)) @disabled(! $user->hasVerifiedEmail())>
    {{ __('Notificare pe email') }}
</label>
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="notify_sms" value="1" @checked($useOld ? old('notify_sms') : ($item?->notify_sms ?? false)) @disabled(blank($user->phone))>
    {{ __('Notificare pe SMS') }}
</label>
