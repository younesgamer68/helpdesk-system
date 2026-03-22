<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MentionNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public TicketReply $reply,
        public User $mentionedBy,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        if (method_exists($notifiable, 'wantsNotification') && ! $notifiable->wantsNotification('mention')) {
            return [];
        }

        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject' => $this->ticket->subject,
            'reply_id' => $this->reply->id,
            'mentioned_by' => $this->mentionedBy->id,
            'mentioned_by_name' => $this->mentionedBy->name,
            'type' => 'mentioned',
            'message' => $this->mentionedBy->name." mentioned you in ticket #{$this->ticket->ticket_number} — {$this->ticket->subject}",
        ];
    }
}
