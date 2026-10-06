@props(['panel'])

@php
    $hasFields = $panel['manufacturers'] !== [] || $panel['filters'] !== [] || $panel['price'] !== null || $panel['stock'] !== null;
@endphp

@if ($hasFields)
    <form method="get" action="{{ route('category', $panel['category']) }}" class="flex flex-col gap-3 text-sm" onchange="this.requestSubmit()">
        @if ($panel['manufacturers'] !== [])
            <fieldset class="min-w-0">
                <legend class="mb-1 font-semibold text-brand">{{ __('Producător') }}</legend>
                <div class="flex flex-col gap-1">
                    @foreach ($panel['manufacturers'] as $maker)
                        <label class="flex items-start gap-2">
                            <input type="checkbox" name="producator[]" value="{{ $maker['name'] }}" @checked($maker['checked']) class="mt-1">
                            <span>{{ __(':name (:count)', ['name' => $maker['name'], 'count' => $maker['count']]) }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endif

        @foreach ($panel['filters'] as $filter)
            <fieldset class="min-w-0">
                <legend class="mb-1 font-semibold text-brand">{{ $filter['name'] }}</legend>
                <div class="flex flex-col gap-1">
                    @foreach ($filter['options'] as $option)
                        <label class="flex items-start gap-2">
                            <input type="checkbox" name="c[{{ $filter['slug'] }}][]" value="{{ $option['slug'] }}" @checked($option['checked']) class="mt-1">
                            <span>{{ __(':label (:count)', ['label' => $option['label'], 'count' => $option['count']]) }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endforeach

        @if ($panel['price'])
            <fieldset class="min-w-0">
                <legend class="mb-1 font-semibold text-brand">{{ __('Preț') }}</legend>
                <div class="flex gap-2">
                    <input type="number" name="pret[min]" value="{{ $panel['price']['min'] }}" placeholder="{{ __('Min') }}" step="any" class="w-full border border-[#e3e3e3] px-2 py-1">
                    <input type="number" name="pret[max]" value="{{ $panel['price']['max'] }}" placeholder="{{ __('Max') }}" step="any" class="w-full border border-[#e3e3e3] px-2 py-1">
                </div>
            </fieldset>
        @endif

        @if ($panel['stock'])
            <label class="flex items-start gap-2">
                <input type="checkbox" name="stoc" value="1" @checked($panel['stock']['checked']) class="mt-1">
                <span>{{ __('În stoc (:count)', ['count' => $panel['stock']['count']]) }}</span>
            </label>
        @endif

        @if ($panel['filtered'])
            <a href="{{ route('category', $panel['category']) }}" class="text-faded underline">{{ __('Resetează') }}</a>
        @endif
    </form>
@endif
