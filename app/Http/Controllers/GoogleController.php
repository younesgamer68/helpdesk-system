<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirige vers Google pour l'authentification
     */
    public function redirect()
    {
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account consent'])
            ->redirect();
    }

    /**
     * Callback Google → crée/connecte l'utilisateur → dashboard
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('register')
                ->withErrors(['email' => 'Google authentication failed. Please try again.']);
        }

        $googleEmail = $googleUser->getEmail();
        $googleId = (string) $googleUser->getId();

        if (! $googleEmail) {
            return redirect()->route('register')
                ->withErrors(['email' => 'Google account did not return an email address.']);
        }

        if (Auth::check()) {
            Auth::logout();
        }

        $user = User::withoutGlobalScopes()
            ->withTrashed()
            ->where('email', $googleEmail)
            ->first();

        if (! $user) {
            $user = User::withoutGlobalScopes()
                ->withTrashed()
                ->where('google_id', $googleId)
                ->first();
        }

        if ($user?->trashed()) {
            return redirect()->route('register')
                ->withErrors(['email' => 'An account with this email already exists but is deactivated. Please contact support.']);
        }

        if ($user) {
            // Pending invited users must finish account setup before login.
            if ($user->password === null) {
                session()->put('pending_user_email', $user->email);

                return redirect()->route('set-password');
            }

            // ── Utilisateur existant (LOGIN) → vérifier email et connecter directement ─────────────
            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            if ($user->google_id !== $googleId || $user->avatar !== $googleUser->getAvatar()) {
                $user->forceFill([
                    'google_id' => $googleId,
                    'avatar' => $googleUser->getAvatar(),
                ])->save();
            }

            Auth::login($user);

        } else {
            // ── Nouvel utilisateur (REGISTER) → créer le compte (email NON vérifié) ──────────────────
            $user = User::create([
                'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'User',
                'email' => $googleEmail,
                'password' => null,
                'google_id' => $googleId,
                'avatar' => $googleUser->getAvatar(),
                // email_verified_at reste null → l'utilisateur doit confirmer
            ]);

            Auth::login($user);

            // Le code de vérification sera envoyé par le composant VerifyEmailCode
        }

        // Rafraîchir l'utilisateur pour avoir les données à jour
        $user->refresh();

        // Si l'email n'est pas vérifié → page de vérification
        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // Si l'utilisateur a une company, rediriger vers le dashboard
        if ($user->company_id && $user->company) {
            return redirect()->route('agent.dashboard', ['company' => $user->company->slug])
                ->with('success', 'Welcome '.$user->name.'!');
        }

        // Sinon, rediriger vers le formulaire de setup company
        return redirect()->route('setup-company');
    }
}
