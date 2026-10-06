<?php

namespace App\Support;

use App\Notifications\PhoneCodeNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class PhoneConfirmation
{
    public const SESSION = 'phone_check';

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function begin(string $phone, string $purpose, array $payload = []): void
    {
        $limiter = 'phone-code-send:'.$phone;

        if (RateLimiter::tooManyAttempts($limiter, 3)) {
            throw ValidationException::withMessages([
                'phone' => 'Prea multe coduri trimise. Încearcă din nou peste câteva minute.',
            ]);
        }

        RateLimiter::hit($limiter, 600);

        $code = (string) random_int(100000, 999999);

        Cache::put(self::cacheKey($phone), [
            'hash' => Hash::make($code),
            'attempts' => 0,
        ], now()->addMinutes(10));

        session([self::SESSION => [
            'phone' => $phone,
            'purpose' => $purpose,
            'payload' => $payload,
            'confirmed' => false,
            'expires_at' => now()->addMinutes(15)->getTimestamp(),
        ]]);

        Notification::route('sms', $phone)->notify(new PhoneCodeNotification($code));
    }

    public static function check(string $code): bool
    {
        $pending = self::pending();

        if ($pending === null) {
            return false;
        }

        $stored = Cache::get(self::cacheKey($pending['phone']));

        if (! is_array($stored)) {
            return false;
        }

        if (($stored['attempts'] ?? 0) >= 5) {
            Cache::forget(self::cacheKey($pending['phone']));

            return false;
        }

        $stored['attempts'] = ($stored['attempts'] ?? 0) + 1;
        Cache::put(self::cacheKey($pending['phone']), $stored, now()->addMinutes(10));

        if (! Hash::check($code, (string) $stored['hash'])) {
            return false;
        }

        Cache::forget(self::cacheKey($pending['phone']));
        session([self::SESSION => [
            ...$pending,
            'confirmed' => true,
        ]]);

        return true;
    }

    /**
     * @return array{phone: string, purpose: string, payload: array<string, mixed>, confirmed: bool, expires_at: int}|null
     */
    public static function pending(): ?array
    {
        $pending = session(self::SESSION);

        if (! is_array($pending) || blank($pending['phone'] ?? null) || blank($pending['purpose'] ?? null)) {
            return null;
        }

        if (($pending['expires_at'] ?? 0) < now()->getTimestamp()) {
            self::forget();

            return null;
        }

        return [
            'phone' => (string) $pending['phone'],
            'purpose' => (string) $pending['purpose'],
            'payload' => is_array($pending['payload'] ?? null) ? $pending['payload'] : [],
            'confirmed' => (bool) ($pending['confirmed'] ?? false),
            'expires_at' => (int) $pending['expires_at'],
        ];
    }

    public static function forget(): void
    {
        session()->forget(self::SESSION);
    }

    private static function cacheKey(string $phone): string
    {
        return 'phone-code:'.$phone;
    }
}
