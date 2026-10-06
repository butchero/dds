<x-site-layout :title="__('Am deja număr la DDS')" :breadcrumbs="\App\Support\Breadcrumbs::forPage(__('Am deja număr la DDS'))">
    <h1 class="mb-2 text-lg font-medium tracking-tight text-brand">{{ __('Am deja număr la DDS') }}</h1>
    <p class="mb-3 max-w-sm text-sm">{{ __('Introdu telefonul primit de la firmă. Îți trimitem un cod pe SMS.') }}</p>
    <form class="max-w-sm space-y-3" method="post" action="{{ route('claim') }}">
        @csrf
        <x-ui.input type="text" name="phone" value="{{ old('phone') }}" required :placeholder="__('Telefon')" />
        @error('phone')
            <p class="text-sm text-brand">{{ $message }}</p>
        @enderror
        <button class="inline-flex h-9 items-center justify-center rounded-lg bg-brand px-3 text-sm font-medium text-white" type="submit">{{ __('Trimite codul') }}</button>
    </form>
</x-site-layout>
