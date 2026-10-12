@php
    $counties = \App\Support\Counties::options();
@endphp

<x-account-page :title="__('Adresele mele')" current="addresses">
    <div class="space-y-3">
        @foreach ($user->addresses as $address)
            <article class="flex gap-3 rounded-lg border border-[#ededed] bg-white p-4 shadow-sm">
                <svg class="mt-0.5 h-6 w-6 shrink-0 text-link" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/>
                </svg>
                <div class="min-w-0">
                    <p class="font-semibold text-[#222222]">{{ $user->name }}@if ($user->phone) - {{ $user->phone }}@endif</p>
                    <p class="mt-1 text-sm text-[#222222]">{{ $address->cardLine() }}</p>
                    <div class="mt-3 flex items-center gap-4 text-sm">
                        <x-account-modal :title="__('Editează adresa')" :label="__('editează')" trigger-class="text-sm text-link underline" :open="$errors->any() && (string) old('address_form') === (string) $address->id">
                            <form class="space-y-3" method="post" action="{{ route('account.addresses.update', $address) }}">
                                @csrf
                                <input type="hidden" name="address_form" value="{{ $address->id }}">
                                @include('account.partials.address-fields', ['address' => $address, 'counties' => $counties])
                                <button class="inline-flex h-9 items-center justify-center rounded-lg bg-primary px-3 text-sm font-medium text-primary-foreground" type="submit">{{ __('Salvează') }}</button>
                            </form>
                        </x-account-modal>
                        <form method="post" action="{{ route('account.addresses.delete', $address) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-sm text-link underline" type="submit">{{ __('șterge') }}</button>
                        </form>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
    <div class="mt-4">
        <x-account-modal :title="__('Adaugă adresa')" :label="__('Adaugă adresa')" :open="$errors->any() && old('address_form') === 'new'">
            <form class="space-y-3" method="post" action="{{ route('account.addresses.store') }}">
                @csrf
                <input type="hidden" name="address_form" value="new">
                @include('account.partials.address-fields', ['address' => null, 'counties' => $counties])
                <button class="inline-flex h-9 items-center justify-center rounded-lg bg-primary px-3 text-sm font-medium text-primary-foreground" type="submit">{{ __('Salvează') }}</button>
            </form>
        </x-account-modal>
    </div>
</x-account-page>
