<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    public ?int $userId = null;

    public function mount()
    {
        $this->userId = Auth::id();
    }

    #[On('notifications-updated')]
    #[On('echo-private:App.Models.User.{userId},.Illuminate\Notifications\Events\BroadcastNotificationCreated')]
    public function refreshNotifications($event = [])
    {
        if (isset($event['message'])) {
            $ticketUrl = null;
            $type = $event['type'] ?? '';

            if (isset($event['ticket_number']) && $type !== 'reassigned') {
                $ticketUrl = route('details', [
                    'company' => Auth::user()->company->slug,
                    'ticket' => $event['ticket_number'],
                ]);
            }

            $title = match ($type) {
                'assigned' => 'Ticket Assigned',
                'reassigned' => 'Ticket Reassigned',
                'client_replied' => 'New Client Reply',
                'status_changed' => 'Status Updated',
                'internal_note' => 'New Internal Note',
                'ticket_submitted' => 'New Ticket',
                'ticket_unassigned' => 'Unassigned Ticket',
                'sla_breached' => 'SLA Breached',
                default => 'New Notification',
            };

            $message = $event['subject'] ?? (collect(explode('—', $event['message']))->last() ?? $event['message']);

            $this->dispatch('show-notification-toast',
                message: $message,
                title: $title,
                url: $ticketUrl,
            );
        }
    }

    public function markRead($id)
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
    }

    public function markAllRead()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
        $this->dispatch('notifications-updated');
    }

    #[Computed]
    public function notifications()
    {
        return Auth::user()->notifications()->latest()->take(10)->get();
    }

    #[Computed]
    public function unreadCount()
    {
        return Auth::user()->unreadNotifications()->count();
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
