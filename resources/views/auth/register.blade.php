<x-site-layout title="Cont nou">
    <h1 class="mb-[5px] font-arial text-[13px] text-portocaliu">Cont nou</h1>
    <form class="max-w-sm space-y-2" method="post" action="{{ route('register') }}">
        @csrf
        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Nume" class="block w-full border border-[#a4a4a4] px-2 py-1">
        @error('name')
            <p class="text-portocaliu">{{ $message }}</p>
        @enderror
        <input type="email" name="email" value="{{ old('email') }}" required placeholder="Email" class="block w-full border border-[#a4a4a4] px-2 py-1">
        @error('email')
            <p class="text-portocaliu">{{ $message }}</p>
        @enderror
        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Telefon" class="block w-full border border-[#a4a4a4] px-2 py-1">
        <input type="password" name="password" required placeholder="Parola" class="block w-full border border-[#a4a4a4] px-2 py-1">
        @error('password')
            <p class="text-portocaliu">{{ $message }}</p>
        @enderror
        <input type="password" name="password_confirmation" required placeholder="Confirma parola" class="block w-full border border-[#a4a4a4] px-2 py-1">
        <button class="bg-portocaliu px-3 py-1 text-white" type="submit">Creeaza cont</button>
    </form>
</x-site-layout>
