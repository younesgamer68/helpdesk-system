<?php

namespace App\Livewire\Settings;

use App\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Password extends Component
{
    use PasswordValidationRules;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $rules = [
                'password' => $this->passwordRules(),
            ];

            if (Auth::user()->password) {
                $rules['current_password'] = $this->currentPasswordRules();
            }

            $validated = $this->validate($rules);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}
