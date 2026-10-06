<x-site-layout :title="__('Finalizează contul')" :breadcrumbs="\App\Support\Breadcrumbs::forPage(__('Finalizează contul'))">
    <h1 class="mb-2 text-lg font-medium tracking-tight text-brand">{{ __('Finalizează contul') }}</h1>
    <form class="max-w-sm space-y-3" method="post" action="{{ route('claim.finish') }}">
        @csrf
        <x-ui.input type="text" name="name" value="{{ old('name', $name) }}" required :placeholder="__('Nume')" />
        @error('name')
            <p class="text-sm text-brand">{{ $message }}</p>
        @enderror
        <x-ui.input type="email" name="email" value="{{ old('email') }}" required :placeholder="__('Email')" />
        @error('email')
            <p class="text-sm text-brand">{{ $message }}</p>
        @enderror
        <x-ui.input type="password" name="password" required :placeholder="__('Parola')" />
        @error('password')
            <p class="text-sm text-brand">{{ $message }}</p>
        @enderror
        <x-ui.input type="password" name="password_confirmation" required :placeholder="__('Confirma parola')" />
        <button class="inline-flex h-9 items-center justify-center rounded-lg bg-brand px-3 text-sm font-medium text-white" type="submit">{{ __('Salvează contul') }}</button>
    </form>
</x-site-layout>
