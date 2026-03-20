<?php

namespace App\Notifications;

use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TeamAssigned extends Notification
{
    use Queueable;

    public function __construct(public Team $team, public string $role = 'member') {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if (method_exists($notifiable, 'wantsNotification') && ! $notifiable->wantsNotification('team_assigned')) {
            return [];
        }

        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
            'role' => $this->role,
            'type' => 'team_assigned',
            'message' => "You have been added to team '{$this->team->name}' as {$this->role}.",
        ];
    }
}
