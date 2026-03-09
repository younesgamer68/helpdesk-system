<?php

namespace App\Mail;

use App\Models\AutomationRule;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EscalationNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public AutomationRule $rule
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Escalation] Ticket #{$this->ticket->ticket_number} requires attention",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.escalation-notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
