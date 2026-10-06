<x-site-layout :title="__('Confirmă telefonul')" :breadcrumbs="\App\Support\Breadcrumbs::forPage(__('Confirmă telefonul'))">
    <h1 class="mb-2 text-lg font-medium tracking-tight text-brand">{{ __('Confirmă telefonul') }}</h1>
    <p class="mb-3 max-w-sm text-sm">{{ __('Am trimis un cod la :phone.', ['phone' => $phone]) }}</p>
    <form class="max-w-sm space-y-3" method="post" action="{{ route('phone.confirm') }}">
        @csrf
        <x-ui.input type="text" name="code" inputmode="numeric" maxlength="6" required :placeholder="__('Cod din SMS')" />
        @error('code')
            <p class="text-sm text-brand">{{ $message }}</p>
        @enderror
        <button class="inline-flex h-9 items-center justify-center rounded-lg bg-brand px-3 text-sm font-medium text-white" type="submit">{{ __('Confirmă') }}</button>
    </form>
</x-site-layout>
