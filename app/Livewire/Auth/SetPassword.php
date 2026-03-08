<?php

namespace App\Livewire\Auth;

use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class SetPassword extends Component
{
    public $password;

    public $password_confirmation;

    public $specialty_id = '';

    public function toJSON()
    {
        return [];
    }

    public function mount()
    {
        // Handled by user.pending middleware
    }

    #[Computed]
    public function pendingUser(): ?User
    {
        $email = session('pending_user_email');

        return User::where('email', $email)->first();
    }

    #[Computed]
    public function categories()
    {
        $user = $this->pendingUser;

        if (! $user || ! $user->company_id) {
            return collect();
        }

        return TicketCategory::where('company_id', $user->company_id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function isOperator(): bool
    {
        $user = $this->pendingUser;

        return $user && $user->role === 'operator';
    }

    public function save()
    {
        $rules = [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        // Only validate specialty for operators
        if ($this->isOperator) {
            $rules['specialty_id'] = ['nullable', 'exists:ticket_categories,id'];
        }

        $this->validate($rules);

        $email = session('pending_user_email');

        /** @var \App\Models\User $user */
        $user = User::where('email', '=', $email)->firstOrFail(['*']);

        $updateData = [
            'password' => Hash::make($this->password),
            'email_verified_at' => now(),
        ];

        // Set specialty for operators
        if ($user->role === 'operator' && $this->specialty_id) {
            $updateData['specialty_id'] = $this->specialty_id;
        }

        $user->update($updateData);

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
