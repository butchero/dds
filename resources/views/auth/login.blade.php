<x-site-layout title="Autentificare">
    <h1 class="mb-[5px] font-arial text-[13px] text-portocaliu">Autentificare</h1>
    <form class="max-w-sm space-y-2" method="post" action="{{ route('login') }}">
        @csrf
        <input type="email" name="email" value="{{ old('email') }}" required placeholder="Email" class="block w-full border border-[#a4a4a4] px-2 py-1">
        @error('email')
            <p class="text-portocaliu">{{ $message }}</p>
        @enderror
        <input type="password" name="password" required placeholder="Parola" class="block w-full border border-[#a4a4a4] px-2 py-1">
        <button class="bg-portocaliu px-3 py-1 text-white" type="submit">Intra in cont</button>
    </form>
</x-site-layout>
