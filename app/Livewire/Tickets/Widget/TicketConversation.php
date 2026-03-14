<?php

namespace App\Livewire\Tickets\Widget;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Notifications\ClientReplied;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class TicketConversation extends Component
{
    use WithFileUploads;

    public Ticket $ticket;

    #[Validate('required|string|max:500')]
    public $message = '';

    #[Validate(['attachments.*' => 'nullable|file|max:2048'])] // 2MB Max
    #[Validate('max:2', message: 'You can only upload a maximum of 2 files.')]
    public $attachments = [];

    public function mount(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function submitReply()
    {
        if (in_array($this->ticket->status, ['closed'])) {
            $this->dispatch('show-toast', message: 'This ticket is closed.', type: 'error');

            return;
        }

        $this->validate();

        $attachmentPaths = [];

        if ($this->attachments) {
            foreach ($this->attachments as $attachment) {
                // Store in a 'ticket-attachments' directory within the public disk
                $path = $attachment->store('ticket-attachments', 'public');
                $attachmentPaths[] = [
                    'name' => $attachment->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $attachment->getMimeType(),
                    'size' => $attachment->getSize(),
                ];
            }
        }

        // Create reply from customer
        TicketReply::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => null, // null means it's from the customer side
            'customer_name' => $this->ticket->customer_name,
            'message' => clean($this->message),
            'is_internal' => false,
            'attachments' => empty($attachmentPaths) ? null : $attachmentPaths,
        ]);

        if ($this->ticket->status !== 'closed') {
            $this->ticket->update(['status' => 'pending']);
        }

        // Refresh ticket to pick up any assignment changes made after the widget was loaded
        $this->ticket->refresh();

        // Notify assigned agent
        if ($this->ticket->assignedTo) {
            $this->ticket->assignedTo->notify(new ClientReplied($this->ticket));
        }

        $this->reset(['message', 'attachments']);
        $this->dispatch('resetEditor');

        // Let the view know we replied
        session()->flash('success', 'Your reply has been submitted!');
    }

    public function removeAttachment($index)
    {
        array_splice($this->attachments, $index, 1);
    }

    public function render()
    {
        return view('livewire.tickets.widget.ticket-conversation', [
            'replies' => TicketReply::where('ticket_id', $this->ticket->id)
                ->where('is_internal', false)
                ->with('user:id,name')
                ->orderBy('created_at', 'asc')
                ->get(),
        ]);
    }
}
