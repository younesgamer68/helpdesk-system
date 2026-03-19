<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Appearance extends Component
{
    public string $accentColor = '#0B4F4A';

    public function mount(): void
    {
        $this->accentColor = Auth::user()->company->accent_color ?? '#0B4F4A';
    }

    public function saveAccentColor(): void
    {
        $this->validate([
            'accentColor' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        Auth::user()->company->update(['accent_color' => $this->accentColor]);

        $this->dispatch('accent-color-saved');
    }
}
