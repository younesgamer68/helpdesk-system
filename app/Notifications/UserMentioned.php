<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserMentioned extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public TicketReply $reply,
        public User $mentionedBy
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'mentioned',
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject' => $this->ticket->subject,
            'ticket_reply_id' => $this->reply->id,
            'mentioned_by_name' => $this->mentionedBy->name,
            'excerpt' => \Illuminate\Support\Str::limit(strip_tags($this->reply->message), 100),
        ];
    }
}
