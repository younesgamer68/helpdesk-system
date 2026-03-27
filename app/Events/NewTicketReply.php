<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class NewTicketReply implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public int $ticketId) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('ticket.'.$this->ticketId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'NewTicketReply';
    }
}
