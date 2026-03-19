<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketClosed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public string $closeReason
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Ticket #'.$this->ticket->ticket_number.' Has Been Closed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-closed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
