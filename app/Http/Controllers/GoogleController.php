<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

        // Cherche si l'utilisateur existe déjà
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // ── Utilisateur existant → juste le connecter ─────────────
            Auth::login($user);

        } else {
            // ── Nouvel utilisateur → créer le compte (email NON vérifié) ──────────────────
            $user = User::create([
                'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'User',
                'email' => $googleUser->getEmail(),
                'password' => bcrypt(Str::random(32)),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                // email_verified_at reste null → l'utilisateur doit confirmer
            ]);

            Auth::login($user);

            // Déclencher l'envoi de l'email de vérification
            event(new Registered($user));
        }

        // Rafraîchir l'utilisateur pour avoir les données à jour
        $user->refresh();

        // Si l'email n'est pas vérifié → page de vérification
        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // Si l'utilisateur a une company, rediriger vers le dashboard
        if ($user->company_id && $user->company) {
            return redirect()->to('http://'.$user->company->slug.'.'.config('app.domain').'/tickets')
                ->with('success', 'Welcome '.$user->name.'!');
        }

        // Sinon, rediriger vers le formulaire de setup company
        return redirect()->route('setup-company');
    }
}
