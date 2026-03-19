<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationPreferences extends Component
{
    /** @var array<string, bool> */
    public array $preferences = [];

    public function mount(): void
    {
        $defaults = [
            'ticket_assigned' => true,
            'ticket_reassigned' => true,
            'client_replied' => true,
            'status_changed' => true,
            'internal_note' => true,
            'ticket_submitted' => true,
        ];

        $saved = Auth::user()->notification_preferences ?? [];

        $this->preferences = array_merge($defaults, $saved);
    }

    public function save(): void
    {
        Auth::user()->update([
            'notification_preferences' => $this->preferences,
        ]);

        $this->dispatch('notification-preferences-saved');
    }
}
