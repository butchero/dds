<x-site-layout title="Contul meu">
    <h1 class="mb-[5px] font-arial text-[13px] text-portocaliu">Contul meu</h1>
    @if (session('status'))
        <p class="text-albastru">{{ session('status') }}</p>
    @endif
    <h2 class="mt-3 font-arial text-[13px] text-portocaliu">Echipamente</h2>
    <ul>
        @foreach ($equipment as $item)
            <li>{{ $item->name }} — ultima revizie {{ $item->last_revision_on?->toDateString() ?? 'necunoscuta' }}, urmatoarea {{ $item->next_revision_on?->toDateString() ?? 'neprogramata' }}</li>
        @endforeach
    </ul>
    <h2 class="mt-3 font-arial text-[13px] text-portocaliu">Programeaza o revizie</h2>
    <form class="max-w-sm space-y-2" method="post" action="{{ route('account.appointment') }}">
        @csrf
        <select name="equipment_id" class="block w-full border border-[#a4a4a4] px-2 py-1">
            <option value="">Alege echipamentul</option>
            @foreach ($equipment as $item)
                <option value="{{ $item->id }}" @selected(old('equipment_id') == $item->id)>{{ $item->name }}</option>
            @endforeach
        </select>
        <input type="date" name="requested_on" value="{{ old('requested_on') }}" class="block w-full border border-[#a4a4a4] px-2 py-1">
        <textarea name="note" placeholder="Observatii" class="block w-full border border-[#a4a4a4] px-2 py-1">{{ old('note') }}</textarea>
        <button class="bg-portocaliu px-3 py-1 text-white" type="submit">Trimite cererea</button>
    </form>
    <h2 class="mt-3 font-arial text-[13px] text-portocaliu">Cereri</h2>
    <ul>
        @foreach ($appointments as $item)
            <li>{{ $item->requested_on?->toDateString() }} — {{ $item->status }}</li>
        @endforeach
    </ul>
    <form method="post" action="{{ route('logout') }}">
        @csrf
        <button class="mt-4 underline" type="submit">Iesire</button>
    </form>
</x-site-layout>
