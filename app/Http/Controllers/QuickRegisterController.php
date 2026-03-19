<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class QuickRegisterController extends Controller
{
    /**
     * Handle quick registration:
     * 1. Validate email
     * 2. Create user account (random password)
     * 3. Auto-login
     * 4. Send welcome email (async)
     * 5. Redirect to dashboard
     */
    public function store(Request $request)
    {
        // ── 1. Validate ──────────────────────────────────────────────
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered. Please log in instead.',
        ]);

        // ── 2. Create user ────────────────────────────────────────────
        // Extract name from email (e.g. john.doe@gmail.com → John Doe)
        $rawName = explode('@', $request->email)[0];
        $name = Str::title(str_replace(['.', '_', '-'], ' ', $rawName));

        $user = User::create([
            'name' => $name,
            'email' => $request->email,
            'password' => Hash::make(Str::random(32)), // Random password — user can set later
        ]);

        // ── 3. Auto-login ─────────────────────────────────────────────
        Auth::login($user);

        // ── 4. Send welcome email (queued) ────────────────────────────
        try {
            Mail::to($user->email)->queue(new WelcomeMail($user));
        } catch (\Exception $e) {
            // Don't block registration if email fails — just log it
            logger()->error('Welcome email failed for user '.$user->id.': '.$e->getMessage());
        }

        // ── 5. Redirect to company setup ─────────────────────────────
        return redirect()->route('setup-company')
            ->with('success', 'Welcome to HelpDesk! Check your email for a confirmation.');
    }
}
