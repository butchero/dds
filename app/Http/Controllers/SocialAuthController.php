<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;

class SocialAuthController extends Controller
{
    public function redirect(string $provider): SymfonyRedirect
    {
        $provider = $this->provider($provider);

        $driver = Socialite::driver($provider)
            ->redirectUrl(route('social.callback', ['provider' => $provider]));

        if ($provider === 'facebook') {
            $driver->scopes(['email']);
        }

        return $driver->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $provider = $this->provider($provider);

        $driver = Socialite::driver($provider)
            ->redirectUrl(route('social.callback', ['provider' => $provider]));

        if ($provider === 'facebook') {
            $driver->scopes(['email']);
        }

        $socialUser = $driver->user();

        $providerId = (string) $socialUser->getId();
        $email = $socialUser->getEmail();

        $linked = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();

        if ($linked) {
            Auth::login($linked->user, true);

            return redirect()->route('account.home');
        }

        if (blank($email)) {
            return redirect()->route('login')->withErrors([
                'email' => __('Contul de socializare nu a trimis o adresă de e-mail.'),
            ]);
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $user = new User;
            $user->forceFill([
                'name' => $socialUser->getName() ?: 'Client',
                'email' => $email,
                'password' => null,
                'role' => 'client',
                'email_verified_at' => now(),
            ])->save();
        } elseif (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $user->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => $providerId,
        ]);

        Auth::login($user, true);

        return redirect()->route('account.home');
    }

    private function provider(string $provider): string
    {
        abort_unless(in_array($provider, ['google', 'facebook'], true), 404);

        return $provider;
    }
}
