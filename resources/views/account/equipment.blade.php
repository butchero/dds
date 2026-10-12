<x-account-page :title="__('Echipamente')" current="equipment">
    <div class="space-y-3">
        @forelse ($user->equipment as $item)
            <article class="rounded-lg border border-[#ededed] bg-white p-4 shadow-sm">
                <p class="font-semibold text-[#222222]">{{ $item->name }}</p>
                <p class="mt-1 text-sm text-[#222222]">{{ $item->address?->cardLine() }}</p>
                <p class="mt-1 text-sm text-[#444444]">{{ __('Data ultimei revizii') }}: {{ $item->last_revision_on?->format('d.m.Y') ?? __('necunoscută') }}</p>
                <p class="text-sm text-[#444444]">{{ __('Următoarea revizie') }}: {{ $item->next_revision_on?->format('d.m.Y') ?? __('neprogramată') }}</p>
                <div class="mt-3 flex items-center gap-4 text-sm">
                    <x-account-modal :title="__('Editează echipamentul')" :label="__('editează')" trigger-class="text-sm text-link underline" :open="$errors->any() && (string) old('equipment_form') === (string) $item->id">
                        <form class="space-y-3" method="post" action="{{ route('account.equipment.update', $item) }}">
                            @csrf
                            <input type="hidden" name="equipment_form" value="{{ $item->id }}">
                            @include('account.partials.equipment-fields', ['item' => $item])
                            <button class="inline-flex h-9 items-center justify-center rounded-lg bg-primary px-3 text-sm font-medium text-primary-foreground" type="submit">{{ __('Salvează') }}</button>
                        </form>
                    </x-account-modal>
                    <form method="post" action="{{ route('account.equipment.delete', $item) }}">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm text-link underline" type="submit">{{ __('șterge') }}</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="text-sm">{{ __('Nu ai echipamente încă.') }}</p>
        @endforelse
    </div>
    @if ($user->addresses->isNotEmpty())
        <div class="mt-4">
            <x-account-modal :title="__('Adaugă echipamentul')" :label="__('Adaugă echipamentul')" :open="$errors->any() && old('equipment_form') === 'new'">
                <form class="space-y-3" method="post" action="{{ route('account.equipment.store') }}">
                    @csrf
                    <input type="hidden" name="equipment_form" value="new">
                    @include('account.partials.equipment-fields', ['item' => null])
                    <button class="inline-flex h-9 items-center justify-center rounded-lg bg-primary px-3 text-sm font-medium text-primary-foreground" type="submit">{{ __('Salvează') }}</button>
                </form>
            </x-account-modal>
        </div>
    @else
        <p class="mt-2 text-sm">{{ __('Adaugă întâi o adresă, apoi echipamentul.') }}</p>
    @endif
</x-account-page>
