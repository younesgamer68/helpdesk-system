<?php

namespace App\Livewire\Auth;

use Livewire\Component;

class Login extends Component
{
    public $email = '';

    public function rules()
    {
        return [
            'email' => 'required|email',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
        ];
    }

    public function submit()
    {
        $this->validate();

        // Logique de connexion
        // Exemple: vérifier si l'utilisateur existe et le connecter
        
        session()->flash('message', 'Check your email to continue!');
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.auth');
    }
}