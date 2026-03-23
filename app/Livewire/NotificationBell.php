<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    #[On('notifications-updated')]
    public function refreshNotifications(): void
    {
        unset($this->notifications, $this->unreadCount);
    }

    public function markRead(string $id): mixed
    {
        $notification = Auth::user()->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();

            if (isset($notification->data['ticket_number'])) {
                $this->dispatch('notifications-updated');

                return redirect()->route('details', [
                    'company' => Auth::user()->company->slug,
                    'ticket' => $notification->data['ticket_number'],
                ]);
            }
        }

        return null;
    }

    public function markAllRead(): void
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
        $this->dispatch('notifications-updated');
    }

    #[Computed]
    public function notifications(): \Illuminate\Database\Eloquent\Collection
    {
        return Auth::user()->notifications()->latest()->take(10)->get();
    }

    #[Computed]
    public function unreadCount(): int
    {
        return Auth::user()->unreadNotifications()->count();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.notification-bell');
    }
}
