<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirige vers Google pour l'authentification
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
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
            // ── Nouvel utilisateur → créer le compte ──────────────────
            $user = User::create([
                'name'              => $googleUser->getName() ?? $googleUser->getNickname() ?? 'User',
                'email'             => $googleUser->getEmail(),
                'password'          => bcrypt(Str::random(32)),
                'google_id'         => $googleUser->getId(),
                'avatar'            => $googleUser->getAvatar(),
                'email_verified_at' => now(), // Google vérifie l'email
            ]);

            Auth::login($user);

            // Envoyer email de bienvenue
            try {
                Mail::to($user->email)->queue(new WelcomeMail($user));
            } catch (\Exception $e) {
                logger()->error('Welcome email failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('ticket')
            ->with('success', 'Welcome ' . $user->name . '!');
    }
}