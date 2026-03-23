<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TicketPriorityChanged extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $ticket;

    public $oldPriority;

    public $newPriority;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, string $oldPriority, string $newPriority)
    {
        $this->ticket = $ticket;
        $this->oldPriority = $oldPriority;
        $this->newPriority = $newPriority;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject' => $this->ticket->subject,
            'type' => 'priority_changed',
            'message' => "Ticket #{$this->ticket->ticket_number} priority changed from {$this->oldPriority} to {$this->newPriority}",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function broadcastType(): string
    {
        return 'priority_changed';
    }
}
