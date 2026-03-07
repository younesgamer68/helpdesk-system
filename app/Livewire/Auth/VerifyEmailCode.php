<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class VerifyEmailCode extends Component
{
    public string $code = '';

    public function mount(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectToNextStep();
        }

        // Send code if not already sent or expired
        $this->sendCodeIfNeeded();
    }

    public function verify(): void
    {
        $this->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();

        $verification = DB::table('email_verification_codes')
            ->where('user_id', $user->id)
            ->where('code', $this->code)
            ->where('expires_at', '>', now())
            ->first();

        if (! $verification) {
            $this->addError('code', 'Invalid or expired code. Please request a new one.');

            return;
        }

        // Mark email as verified
        $user->markEmailAsVerified();

        // Delete the used code
        DB::table('email_verification_codes')
            ->where('user_id', $user->id)
            ->delete();

        $this->redirectToNextStep();
    }

    public function resendCode(): void
    {
        $user = Auth::user();

        // Delete old codes
        DB::table('email_verification_codes')
            ->where('user_id', $user->id)
            ->delete();

        // Generate new code
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('email_verification_codes')->insert([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinute(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Send email
        Mail::to($user->email)->send(new \App\Mail\VerificationCode($code));

        session()->flash('status', 'verification-code-sent');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect(route('login'));
    }

    protected function sendCodeIfNeeded(): void
    {
        $user = Auth::user();

        $existingCode = DB::table('email_verification_codes')
            ->where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->first();

        if (! $existingCode) {
            $this->resendCode();
        }
    }

    protected function redirectToNextStep(): void
    {
        $user = Auth::user();

        if ($user->company_id && $user->company) {
            $this->redirect('http://'.$user->company->slug.'.'.config('app.domain').'/tickets');
        } else {
            $this->redirect(route('setup-company'));
        }
    }

    public function render()
    {
        return view('livewire.auth.verify-email-code');
    }
}
