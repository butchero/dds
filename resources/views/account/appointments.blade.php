@php
    $button = 'inline-flex h-9 items-center justify-center rounded-lg bg-brand px-3 text-sm font-medium text-white';
@endphp

<x-account-page :title="__('Programări')" current="appointments">
    <h2 class="text-lg font-medium tracking-tight text-brand">{{ __('Programează o intervenție') }}</h2>
    <form class="mt-2 max-w-sm space-y-3" method="post" action="{{ route('account.appointment') }}">
        @csrf
        <x-ui.select name="address_id" required>
            <option value="">{{ __('Alege adresa') }}</option>
            @foreach ($user->addresses as $address)
                <option value="{{ $address->id }}" @selected(old('address_id') == $address->id)>{{ $address->line() }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select name="billing_type" required>
            <option value="individual" @selected(old('billing_type', 'individual') === 'individual')>{{ __('Persoană fizică') }}</option>
            <option value="company" @selected(old('billing_type') === 'company')>{{ __('Persoană juridică') }}</option>
        </x-ui.select>
        @if ($individualProfiles->count() > 1)
            <x-ui.select name="individual_profile_id">
                <option value="">{{ __('Alege persoana') }}</option>
                @foreach ($individualProfiles as $profile)
                    <option value="{{ $profile->id }}">{{ $profile->displayName() }}</option>
                @endforeach
            </x-ui.select>
        @endif
        @if ($companyProfiles->count() > 1)
            <x-ui.select name="company_profile_id">
                <option value="">{{ __('Alege firma') }}</option>
                @foreach ($companyProfiles as $profile)
                    <option value="{{ $profile->id }}">{{ $profile->displayName() }}</option>
                @endforeach
            </x-ui.select>
        @endif
        <x-ui.select name="equipment_id">
            <option value="">{{ __('Echipament, dacă e cazul') }}</option>
            @foreach ($user->equipment as $item)
                <option value="{{ $item->id }}">{{ $item->name }} — {{ $item->address?->line() }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.input type="date" name="requested_on" value="{{ old('requested_on') }}" />
        <x-ui.textarea name="note" :placeholder="__('Observații')">{{ old('note') }}</x-ui.textarea>
        <button class="{{ $button }}" type="submit">{{ __('Trimite cererea') }}</button>
    </form>

    <h2 class="mt-6 text-lg font-medium tracking-tight text-brand">{{ __('Istoric programări') }}</h2>
    <ul class="mt-2 space-y-2 text-sm">
        @forelse ($user->appointments as $item)
            <li>
                {{ $item->requested_on?->format('d.m.Y') ?? __('fără dată') }}
                — {{ \App\Models\Appointment::statuses()[$item->status] ?? $item->status }}
                — {{ $item->address?->line() }}
                — {{ $item->billingProfile?->displayName() }}
            </li>
        @empty
            <li>{{ __('Nu ai programări încă.') }}</li>
        @endforelse
    </ul>
</x-account-page>
