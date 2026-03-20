<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('My Teams')]
class MyTeam extends Component
{
    #[Computed]
    public function teams()
    {
        return Auth::user()->teams()
            ->with(['members' => fn ($q) => $q->orderBy('name')])
            ->withCount('members', 'tickets')
            ->get();
    }

    public function render()
    {
        return view('livewire.settings.my-team');
    }
}
