@php
    $button = 'inline-flex h-9 items-center justify-center rounded-lg bg-brand px-3 text-sm font-medium text-white';
@endphp

<x-account-page :title="__('Echipamente')" current="equipment">
    @forelse ($user->equipment as $item)
        <form class="mt-3 max-w-sm space-y-3 border border-hairline p-3" method="post" action="{{ route('account.equipment.update', $item) }}">
            @csrf
            <x-ui.input type="text" name="name" value="{{ $item->name }}" required :placeholder="__('Nume echipament')" />
            <x-ui.input type="text" name="type" value="{{ $item->type }}" :placeholder="__('Tip, de exemplu centrală')" />
            <x-ui.select name="address_id" required>
                <option value="">{{ __('Adresă') }}</option>
                @foreach ($user->addresses as $address)
                    <option value="{{ $address->id }}" @selected($item->address_id === $address->id)>{{ $address->line() }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.select name="interval_months" required>
                <option value="12" @selected($item->interval_months === 12)>{{ __('12 luni') }}</option>
                <option value="24" @selected($item->interval_months === 24)>{{ __('24 luni') }}</option>
            </x-ui.select>
            <x-ui.input type="date" name="last_revision_on" value="{{ $item->last_revision_on?->toDateString() }}" />
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="notify_email" value="1" @checked($item->notify_email) @disabled(! $user->hasVerifiedEmail())>
                {{ __('Notificare pe email') }}
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="notify_sms" value="1" @checked($item->notify_sms) @disabled(blank($user->phone))>
                {{ __('Notificare pe SMS') }}
            </label>
            <p class="text-sm">{{ __('Următoarea revizie') }}: {{ $item->next_revision_on?->format('d.m.Y') ?? __('neprogramată') }}</p>
            <button class="{{ $button }}" type="submit">{{ __('Salvează echipamentul') }}</button>
        </form>
        <form method="post" action="{{ route('account.equipment.delete', $item) }}">
            @csrf
            @method('DELETE')
            <button class="mt-1 text-sm underline" type="submit">{{ __('Șterge echipamentul') }}</button>
        </form>
    @empty
        <p class="text-sm">{{ __('Nu ai echipamente încă.') }}</p>
    @endforelse
    @if ($user->addresses->isNotEmpty())
        <form class="mt-3 max-w-sm space-y-3" method="post" action="{{ route('account.equipment.store') }}">
            @csrf
            <x-ui.input type="text" name="name" required :placeholder="__('Nume echipament')" />
            <x-ui.input type="text" name="type" :placeholder="__('Tip, de exemplu centrală')" />
            <x-ui.select name="address_id" required>
                <option value="">{{ __('Adresă') }}</option>
                @foreach ($user->addresses as $address)
                    <option value="{{ $address->id }}">{{ $address->line() }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.select name="interval_months" required>
                <option value="12">{{ __('12 luni') }}</option>
                <option value="24" selected>{{ __('24 luni') }}</option>
            </x-ui.select>
            <x-ui.input type="date" name="last_revision_on" />
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="notify_email" value="1" @disabled(! $user->hasVerifiedEmail())>
                {{ __('Notificare pe email') }}
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="notify_sms" value="1" @disabled(blank($user->phone))>
                {{ __('Notificare pe SMS') }}
            </label>
            <button class="{{ $button }}" type="submit">{{ __('Adaugă echipamentul') }}</button>
        </form>
    @else
        <p class="mt-2 text-sm">{{ __('Adaugă întâi o adresă, apoi echipamentul.') }}</p>
    @endif
</x-account-page>
