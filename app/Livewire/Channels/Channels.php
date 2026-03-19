<?php

namespace App\Livewire\Channels;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Integrations')]
class Channels extends Component
{
    public string $activeTab = 'form_widget';

    public function mount(): void
    {
        abort_unless(Auth::user()?->role === 'admin', 403);
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['form_widget', 'ai_chatbot_widget', 'kb_widget'], true)) {
            return;
        }

        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.channels.channels');
    }
}
