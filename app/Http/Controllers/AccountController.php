<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function login(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, true)) {
            return back()->withErrors(['email' => 'Datele de autentificare nu sunt corecte.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('account.home');
    }

    public function register(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::query()->create([
            ...$data,
            'role' => 'client',
        ]);

        Auth::login($user);

        return redirect()->route('account.home');
    }

    public function home(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Account/Dashboard', [
            'equipment' => $user->equipment()->get(['id', 'name', 'last_revision_on', 'next_revision_on']),
            'appointments' => $user->appointments()->latest()->get(['id', 'requested_on', 'status']),
        ]);
    }

    public function appointment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'equipment_id' => ['nullable', 'integer'],
            'requested_on' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $equipmentId = $data['equipment_id'] ?: null;

        if ($equipmentId && ! $request->user()->equipment()->whereKey($equipmentId)->exists()) {
            abort(403);
        }

        Appointment::query()->create([
            'user_id' => $request->user()->id,
            'equipment_id' => $equipmentId,
            'requested_on' => $data['requested_on'] ?? null,
            'note' => $data['note'] ?? null,
            'status' => 'requested',
        ]);

        return redirect()->route('account.home')->with('status', 'Cererea de programare a fost trimisa.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
