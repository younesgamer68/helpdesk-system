<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class SetPassword extends Component
{
    public $password;

    public $password_confirmation;

    public function toJSON()
    {
        return [];
    }

    public function mount()
    {
        // Handled by user.pending middleware
    }

    public function save()
    {
        $this->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = session('pending_user_email');

        /** @var \App\Models\User $user */
        $user = User::where('email', '=', $email)->firstOrFail(['*']);

        $user->update([
            'password' => Hash::make($this->password),
            'email_verified_at' => now(),
        ]);

        Auth::login($user);
        session()->forget('pending_user_email');

        // Redirect to the company's subdomain tickets page
        return redirect()->route('tickets', ['company' => $user->company->slug]);
    }

    public function render()
    {
        return view('livewire.auth.set-password');
    }
}
