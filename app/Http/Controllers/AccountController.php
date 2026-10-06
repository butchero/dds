<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BillingProfile;
use App\Models\Equipment;
use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\AppointmentRequestedNotification;
use App\Support\PhoneConfirmation;
use App\Support\PhoneNumber;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function login(): View
    {
        return view('auth.login');
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

    public function register(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $phone = $this->phone($data['phone'] ?? null);
        $existing = $phone ? User::query()->where('phone', $phone)->first() : null;

        if ($existing && ! $existing->isUnclaimed()) {
            throw ValidationException::withMessages([
                'phone' => 'Acest telefon este deja folosit.',
            ]);
        }

        $emailTaken = User::query()
            ->where('email', $data['email'])
            ->when($existing, fn ($query) => $query->whereKeyNot($existing->id))
            ->exists();

        if ($emailTaken) {
            throw ValidationException::withMessages([
                'email' => 'Această adresă de e-mail este deja folosită.',
            ]);
        }

        if ($phone) {
            PhoneConfirmation::begin($phone, 'register', [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'user_id' => $existing?->id,
            ]);

            return redirect()->route('phone.confirm');
        }

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'client',
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('verification.notice');
    }

    public function claim(): View
    {
        return view('auth.claim');
    }

    public function startClaim(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
        ]);

        $phone = $this->phone($data['phone']);
        $user = User::query()->where('phone', $phone)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => 'Nu găsim acest număr. Poți crea un cont nou.',
            ]);
        }

        if (! $user->isUnclaimed()) {
            throw ValidationException::withMessages([
                'phone' => 'Acest număr are deja un cont. Autentifică-te.',
            ]);
        }

        PhoneConfirmation::begin($phone, 'claim', [
            'user_id' => $user->id,
            'name' => $user->name,
        ]);

        return redirect()->route('phone.confirm');
    }

    public function finishClaim(): View
    {
        $pending = $this->confirmedClaim();

        return view('auth.claim-finish', [
            'name' => $pending['payload']['name'] ?? '',
        ]);
    }

    public function storeClaim(Request $request): RedirectResponse
    {
        $pending = $this->confirmedClaim();
        $user = User::query()->findOrFail($pending['payload']['user_id'] ?? 0);
        abort_unless($user->isUnclaimed(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'client',
        ])->save();

        Auth::login($user);
        PhoneConfirmation::forget();

        return redirect()->route('verification.notice');
    }

    public function home(Request $request): View
    {
        return view('account.home', [
            'user' => $request->user(),
        ]);
    }

    public function addresses(Request $request): View
    {
        $user = $request->user();
        $user->load('addresses');

        return view('account.addresses', [
            'user' => $user,
        ]);
    }

    public function equipment(Request $request): View
    {
        $user = $request->user();
        $user->load(['addresses', 'equipment.address']);

        return view('account.equipment', [
            'user' => $user,
        ]);
    }

    public function billing(Request $request): View
    {
        $user = $request->user();
        $user->load(['addresses', 'billingProfiles']);

        return view('account.billing', [
            'user' => $user,
        ]);
    }

    public function appointments(Request $request): View
    {
        $user = $request->user();
        $user->load([
            'addresses',
            'billingProfiles',
            'equipment.address',
            'appointments' => fn ($query) => $query->latest(),
            'appointments.address',
            'appointments.billingProfile',
            'appointments.equipment',
        ]);

        return view('account.appointments', [
            'user' => $user,
            'individualProfiles' => $user->billingProfiles->where('type', 'individual')->values(),
            'companyProfiles' => $user->billingProfiles->where('type', 'company')->values(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->fill($data)->save();

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return back()->with('status', 'Datele au fost salvate.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $rules = [
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ];

        if (filled($request->user()->password)) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $data = $request->validate($rules);
        $request->user()->update(['password' => $data['password']]);

        return back()->with('status', 'Parola a fost schimbată.');
    }

    public function updatePhone(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
        ]);

        $phone = $this->phone($data['phone']);
        $user = $request->user();

        if ($user->phone === $phone) {
            return back()->with('status', 'Telefonul este deja salvat.');
        }

        $existing = User::query()->where('phone', $phone)->whereKeyNot($user->id)->first();

        if ($existing && ! $existing->isUnclaimed()) {
            throw ValidationException::withMessages([
                'phone' => 'Acest telefon este deja folosit.',
            ]);
        }

        PhoneConfirmation::begin($phone, 'attach', [
            'user_id' => $existing?->id,
        ]);

        return redirect()->route('phone.confirm');
    }

    public function appointment(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'address_id' => ['required', 'integer'],
            'billing_type' => ['required', Rule::in(['individual', 'company'])],
            'individual_profile_id' => ['nullable', 'integer'],
            'company_profile_id' => ['nullable', 'integer'],
            'equipment_id' => ['nullable', 'integer'],
            'requested_on' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $address = $user->addresses()->whereKey($data['address_id'])->first();

        if (! $address) {
            throw ValidationException::withMessages([
                'address_id' => 'Alege una dintre adresele tale.',
            ]);
        }

        $profiles = $user->billingProfiles()->where('type', $data['billing_type'])->get();

        if ($profiles->isEmpty()) {
            throw ValidationException::withMessages([
                'billing_type' => 'Adaugă mai întâi un profil de facturare de tipul ales.',
            ]);
        }

        $selectedId = $data['billing_type'] === 'company'
            ? (int) ($data['company_profile_id'] ?? 0)
            : (int) ($data['individual_profile_id'] ?? 0);

        $profile = $profiles->count() === 1
            ? $profiles->first()
            : $profiles->firstWhere('id', $selectedId);

        if (! $profile instanceof BillingProfile) {
            throw ValidationException::withMessages([
                'billing_type' => 'Alege profilul de facturare.',
            ]);
        }

        $equipmentId = ($data['equipment_id'] ?? null) ?: null;

        if ($equipmentId) {
            $equipment = $user->equipment()->whereKey($equipmentId)->first();

            if (! $equipment instanceof Equipment || (int) $equipment->address_id !== (int) $address->id) {
                throw ValidationException::withMessages([
                    'equipment_id' => 'Echipamentul trebuie să fie la adresa aleasă.',
                ]);
            }
        }

        $appointment = Appointment::query()->create([
            'user_id' => $user->id,
            'address_id' => $address->id,
            'billing_profile_id' => $profile->id,
            'equipment_id' => $equipmentId,
            'requested_on' => $data['requested_on'] ?? null,
            'note' => $data['note'] ?? null,
            'status' => 'requested',
        ]);

        $companyEmail = SiteSetting::get('company_email');

        if (filled($companyEmail)) {
            Notification::route('mail', $companyEmail)->notify(new AppointmentRequestedNotification($appointment));
        }

        return redirect()->route('account.appointments')->with('status', 'Cererea de programare a fost trimisă.');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        if ($user && ! $user->hasVerifiedEmail()) {
            return back()->withErrors([
                'email' => 'Confirmă mai întâi adresa de e-mail.',
            ])->onlyInput('email');
        }

        $status = Password::sendResetLink($data);

        return $status === Password::ResetLinkSent
            ? back()->with('status', 'Ți-am trimis un mesaj cu linkul de resetare.')
            : back()->withErrors(['email' => 'Nu am putut trimite linkul. Verifică adresa.'])->onlyInput('email');
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                ])->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));
            },
        );

        return $status === Password::PasswordReset
            ? redirect()->route('login')->with('status', 'Parola a fost resetată.')
            : back()->withErrors(['email' => 'Linkul de resetare nu mai este valabil.']);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function phone(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $phone = PhoneNumber::normalize($value);

        if ($phone === null) {
            throw ValidationException::withMessages([
                'phone' => 'Telefonul trebuie să fie un număr de mobil din România.',
            ]);
        }

        return $phone;
    }

    /**
     * @return array{phone: string, purpose: string, payload: array<string, mixed>, confirmed: bool, expires_at: int}
     */
    private function confirmedClaim(): array
    {
        $pending = PhoneConfirmation::pending();
        abort_unless($pending && $pending['purpose'] === 'claim' && $pending['confirmed'], 403);

        return $pending;
    }
}
