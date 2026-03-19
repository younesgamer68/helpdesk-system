<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketClosedWarning extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public int $remainingHours
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Ticket #'.$this->ticket->ticket_number.' Will Be Closed Soon',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-closed-warning',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
