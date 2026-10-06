<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\PhoneConfirmation;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PhoneConfirmationController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $pending = PhoneConfirmation::pending();

        if ($pending === null) {
            return redirect()->route('login')->withErrors([
                'phone' => 'Codul a expirat. Începe din nou.',
            ]);
        }

        if ($pending['confirmed'] && $pending['purpose'] === 'claim') {
            return redirect()->route('claim.finish');
        }

        return view('auth.phone-code', [
            'phone' => $pending['phone'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $pending = PhoneConfirmation::pending();

        if ($pending === null) {
            return redirect()->route('login')->withErrors([
                'phone' => 'Codul a expirat. Începe din nou.',
            ]);
        }

        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        if (! PhoneConfirmation::check($data['code'])) {
            throw ValidationException::withMessages([
                'code' => 'Codul nu este corect sau a expirat.',
            ]);
        }

        $pending = PhoneConfirmation::pending();

        return match ($pending['purpose'] ?? '') {
            'register' => $this->completeRegistration($pending),
            'attach' => $this->completePhone($pending),
            'claim' => redirect()->route('claim.finish'),
            default => redirect()->route('login'),
        };
    }

    /**
     * @param  array{phone: string, purpose: string, payload: array<string, mixed>, confirmed: bool, expires_at: int}  $pending
     */
    private function completeRegistration(array $pending): RedirectResponse
    {
        $payload = $pending['payload'];
        $userId = $payload['user_id'] ?? null;

        if ($userId) {
            $user = User::query()->findOrFail($userId);
            abort_unless($user->isUnclaimed(), 403);
            $user->forceFill([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'password' => $payload['password'],
                'phone' => $pending['phone'],
                'role' => 'client',
            ])->save();
        } else {
            $user = User::query()->create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'password' => $payload['password'],
                'phone' => $pending['phone'],
                'role' => 'client',
            ]);

            event(new Registered($user));
        }

        Auth::login($user);
        PhoneConfirmation::forget();

        return redirect()->route('verification.notice');
    }

    /**
     * @param  array{phone: string, purpose: string, payload: array<string, mixed>, confirmed: bool, expires_at: int}  $pending
     */
    private function completePhone(array $pending): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        $importedId = $pending['payload']['user_id'] ?? null;

        if ($importedId) {
            $imported = User::query()->find($importedId);

            if ($imported instanceof User && $imported->isUnclaimed()) {
                $user->absorb($imported);
            }
        }

        if ($user->phone !== $pending['phone']) {
            $user->forceFill(['phone' => $pending['phone']])->save();
        }

        PhoneConfirmation::forget();

        return redirect()->route('account.home')->with('status', 'Telefonul a fost confirmat.');
    }
}
