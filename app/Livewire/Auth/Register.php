<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class Register extends Component
{
    public $email = '';
    public $company_name = '';
    public $password = '';
    public $password_confirmation = '';
    public $step = 1; // 1 pour email, 2 pour formulaire complet

    public function rules()
    {
        if ($this->step == 1) {
            return [
                'email' => 'required|email|unique:users,email',
            ];
        } else {
            return [
                'company_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ];
        }
    }

    public function messages()
    {
        return [
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already in use.',
            'company_name.required' => 'Company name is required.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Passwords do not match.',
        ];
    }

    public function submitEmail()
    {
        // Valider uniquement l'email à l'étape 1
        $this->validate([
            'email' => 'required|email|unique:users,email',
        ]);

        // Passer à l'étape 2
        $this->step = 2;
    }

    public function backToStep1()
    {
        // Revenir à l'étape 1
        $this->step = 1;
    }

    public function submit()
    {
        // Valider le formulaire complet
        $this->validate();

        try {
            // Créer le nouvel utilisateur
            $user = User::create([
                'name' => $this->company_name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);

            // Connecter automatiquement l'utilisateur
            Auth::login($user);

            session()->flash('message', 'Welcome! Your account has been created successfully.');
            
            // Rediriger vers le dashboard
            return redirect()->route('dashboard');
            
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while creating your account.');
            return null;
        }
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('layouts.auth');
    }
}