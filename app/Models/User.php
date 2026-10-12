<?php

namespace App\Models;

use App\Support\PhoneNumber;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;

#[Fillable(['name', 'email', 'password', 'phone', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if (blank($user->phone)) {
                $user->phone = null;

                return;
            }

            $phone = PhoneNumber::normalize($user->phone);

            if ($phone === null) {
                throw ValidationException::withMessages([
                    'phone' => __('Telefonul trebuie să fie un număr de mobil din România.'),
                ]);
            }

            $taken = static::query()
                ->where('phone', $phone)
                ->when($user->exists, fn ($query) => $query->whereKeyNot($user->id))
                ->exists();

            if ($taken) {
                throw ValidationException::withMessages([
                    'phone' => 'Acest telefon este deja folosit.',
                ]);
            }

            $user->phone = $phone;
        });

        static::updating(function (User $user): void {
            if ($user->isDirty('email') && ! $user->isDirty('email_verified_at')) {
                $user->email_verified_at = null;
            }
        });

        static::updated(function (User $user): void {
            if ($user->wasChanged('email') && filled($user->email) && $user->email_verified_at === null) {
                $user->sendEmailVerificationNotification();
            }
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin';
    }

    public function isUnclaimed(): bool
    {
        return blank($this->password) && $this->socialAccounts()->doesntExist();
    }

    public function absorb(User $imported): void
    {
        if ($imported->is($this)) {
            return;
        }

        $phone = $imported->phone;
        $imported->forceFill(['phone' => null])->save();
        $imported->addresses()->update(['user_id' => $this->id]);
        $imported->equipment()->update(['user_id' => $this->id]);
        $imported->billingProfiles()->update(['user_id' => $this->id]);
        $imported->appointments()->update(['user_id' => $this->id]);
        $imported->socialAccounts()->update(['user_id' => $this->id]);
        $imported->delete();

        if (blank($this->phone) && filled($phone)) {
            $this->forceFill(['phone' => $phone])->save();
        }
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function billingProfiles(): HasMany
    {
        return $this->hasMany(BillingProfile::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function routeNotificationForSms(): ?string
    {
        return $this->phone;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
