<x-account-page :title="__('Programări')" current="appointments">
    <h2 class="text-lg font-medium tracking-tight text-brand">{{ __('Programează o intervenție') }}</h2>
    <div class="mt-2" data-appointment-form>
        <script type="application/json" data-appointment-payload>@json($booking)</script>
        @if ($user->equipment->isEmpty())
            <p>{{ __('Nu ai niciun echipament. Adaugă-l înainte să trimiți cererea.') }}</p>
        @endif
    </div>

    <h2 class="mt-6 text-lg font-medium tracking-tight text-brand">{{ __('Istoric programări') }}</h2>
    <ul class="mt-2 space-y-2 text-sm">
        @forelse ($user->appointments as $item)
            <li>
                {{ $item->requested_on?->format('d.m.Y') ?? __('fără dată') }}
                - {{ \App\Models\Appointment::statuses()[$item->status] ?? $item->status }}
                - {{ $item->address?->line() }}
                - {{ $item->billingProfile?->displayName() }}
                @if ($item->equipment)
                    - {{ $item->equipment->name }}
                @endif
            </li>
        @empty
            <li>{{ __('Nu ai programări încă.') }}</li>
        @endforelse
    </ul>
</x-account-page>
