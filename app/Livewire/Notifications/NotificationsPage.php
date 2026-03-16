<?php

namespace App\Livewire\Notifications;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Notifications')]
class NotificationsPage extends Component
{
    public string $activeTab = 'all';

    public int $perPage = 20;

    public ?int $userId = null;

    public function mount(): void
    {
        $this->userId = Auth::id();
    }

    #[On('notifications-updated')]
    #[On('echo-private:App.Models.User.{userId},.Illuminate\Notifications\Events\BroadcastNotificationCreated')]
    public function onNewNotification(): void
    {
        unset($this->notifications, $this->groupedNotifications, $this->unreadCount, $this->hasMoreNotifications);
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['system', 'sla']) && Auth::user()->role !== 'admin') {
            return;
        }

        $this->activeTab = $tab;
        $this->perPage = 20;
    }

    public function loadMore(): void
    {
        $this->perPage += 20;
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
        unset($this->notifications, $this->groupedNotifications, $this->unreadCount, $this->hasMoreNotifications);
        $this->dispatch('notifications-updated');
    }

    public function clearAll(): void
    {
        Auth::user()->notifications()->delete();
        unset($this->notifications, $this->groupedNotifications, $this->unreadCount, $this->hasMoreNotifications);
        $this->dispatch('notifications-updated');
    }

    #[Computed]
    public function notifications(): Collection
    {
        $query = Auth::user()->notifications()->latest();

        match ($this->activeTab) {
            'unread' => $query->whereNull('read_at'),
            'assigned' => $query->where(function ($q) {
                $q->whereJsonContains('data->type', 'assigned')
                    ->orWhereJsonContains('data->type', 'reassigned');
            }),
            'replies' => $query->whereJsonContains('data->type', 'client_replied'),
            'system' => $query->where(function ($q) {
                $q->whereJsonContains('data->type', 'ticket_submitted')
                    ->orWhereJsonContains('data->type', 'ticket_unassigned');
            }),
            'sla' => $query->whereJsonContains('data->type', 'sla_breached'),
            default => null,
        };

        return $query->take($this->perPage)->get();
    }

    #[Computed]
    public function totalCount(): int
    {
        $query = Auth::user()->notifications();

        match ($this->activeTab) {
            'unread' => $query->whereNull('read_at'),
            'assigned' => $query->where(function ($q) {
                $q->whereJsonContains('data->type', 'assigned')
                    ->orWhereJsonContains('data->type', 'reassigned');
            }),
            'replies' => $query->whereJsonContains('data->type', 'client_replied'),
            'system' => $query->where(function ($q) {
                $q->whereJsonContains('data->type', 'ticket_submitted')
                    ->orWhereJsonContains('data->type', 'ticket_unassigned');
            }),
            'sla' => $query->whereJsonContains('data->type', 'sla_breached'),
            default => null,
        };

        return $query->count();
    }

    #[Computed]
    public function hasMoreNotifications(): bool
    {
        return $this->totalCount > $this->perPage;
    }

    #[Computed]
    public function unreadCount(): int
    {
        return Auth::user()->unreadNotifications()->count();
    }

    /**
     * @return array<string, Collection>
     */
    #[Computed]
    public function groupedNotifications(): array
    {
        $groups = [];
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $weekAgo = Carbon::now()->subWeek();

        foreach ($this->notifications as $notification) {
            $date = $notification->created_at;

            if ($date->isToday()) {
                $label = 'Today';
            } elseif ($date->isYesterday()) {
                $label = 'Yesterday';
            } elseif ($date->isAfter($weekAgo)) {
                $label = 'This Week';
            } else {
                $label = 'Older';
            }

            $groups[$label][] = $notification;
        }

        return $groups;
    }

    public function render()
    {
        return view('livewire.notifications.notifications-page');
    }
}
