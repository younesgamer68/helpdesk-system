<?php

namespace App\Livewire\Dashboard;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class TicketDetails extends Component
{
    use WithFileUploads;

    public Ticket $ticket;

    public $state = '';

    public $senderId = null;

    public $agentSearch = '';

    #[Validate('required|string|max:5000')]
    public $message = '';

    public $internalNote = '';

    #[Validate(['attachments.*' => 'nullable|file|max:10240'])] // 10MB Max for admins
    public $attachments = [];

    public function mount(Ticket $ticket)
    {
        $this->ticket = $ticket->load(['user:id,name,email', 'category:id,name,description']);
        $this->state = $ticket->status;
    }

    #[Computed]
    public function agents()
    {
        $query = User::where('company_id', '=', Auth::user()->company_id)
            ->select('id', 'name', 'email')
            ->orderBy('name');

        if (! empty($this->agentSearch)) {
            $query->where('name', 'like', '%'.$this->agentSearch.'%');
        }

        return $query->get();
    }

    public function resolve()
    {
        if ($this->ticket->status === 'resolved') {
            $this->dispatch('show-toast', message: 'Ticket is already resolved!', type: 'error');

            return;
        }

        $this->ticket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        $this->ticket->refresh();
        $this->state = $this->ticket->status;

        $this->dispatch('show-toast', message: 'Ticket marked as resolved!', type: 'success');
    }

    public function unresolve()
    {
        if ($this->ticket->status !== 'resolved') {
            $this->dispatch('show-toast', message: 'Ticket is not resolved!', type: 'error');

            return;
        }

        $this->ticket->update([
            'status' => 'in_progress',
            'resolved_at' => null,
        ]);

        $this->ticket->refresh();
        $this->state = $this->ticket->status;

        $this->dispatch('show-toast', message: 'Ticket reopened!', type: 'success');
    }

    public function assign($agentId)
    {
        $agent = $this->agents()->find($agentId);

        if ($this->ticket->assigned_to === $agentId) {
            $this->dispatch('show-toast', message: "Ticket is already assigned to {$agent->name}!", type: 'error');

            return;
        }

        $this->ticket->update(['assigned_to' => $agentId]);
        $this->ticket->refresh();

        $this->dispatch('show-toast', message: "Ticket assigned to {$agent->name}!", type: 'success');
    }

    public function changePriority($priority)
    {
        $normalizedPriority = strtolower($priority);

        if ($this->ticket->priority === $normalizedPriority) {
            $this->dispatch('show-toast', message: "Ticket is already prioritized as {$priority}!", type: 'error');

            return;
        }

        if (! in_array($normalizedPriority, ['low', 'medium', 'high', 'urgent'])) {
            $this->dispatch('show-toast', message: 'Invalid priority level!', type: 'error');

            return;
        }

        $this->ticket->update(['priority' => $normalizedPriority]);
        $this->ticket->refresh();

        $this->dispatch('show-toast', message: 'Priority changed to '.ucfirst($normalizedPriority).'!', type: 'success');
    }

    public function changeStatus($status)
    {
        $dbStatus = str_replace(' ', '_', strtolower($status));

        if ($this->ticket->status === $dbStatus) {
            $this->dispatch('show-toast', message: "Ticket is already in '".str_replace('_', ' ', $status)."' status!", type: 'error');

            return;
        }

        $validStatuses = ['pending', 'open', 'in_progress', 'resolved', 'closed'];

        if (! in_array($dbStatus, $validStatuses)) {
            $this->dispatch('show-toast', message: 'Invalid status!', type: 'error');

            return;
        }

        $this->ticket->update(['status' => $dbStatus]);
        $this->ticket->refresh();
        $this->state = $this->ticket->status;

        $this->dispatch('show-toast', message: 'Status changed to '.str_replace('_', ' ', ucfirst($dbStatus)).'!', type: 'success');
    }

    public function closeTicket()
    {
        if ($this->ticket->status === 'closed') {
            $this->dispatch('show-toast', message: 'Ticket is already closed!', type: 'error');

            return;
        }

        if ($this->ticket->status !== 'resolved') {
            $this->dispatch('show-toast', message: 'Ticket must be resolved before closing!', type: 'error');

            return;
        }

        $this->ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        $this->ticket->refresh();
        $this->state = $this->ticket->status;

        $this->dispatch('show-toast', message: 'Ticket closed successfully!', type: 'success');
    }

    public function addReply()
    {
        if ($this->ticket->status === 'closed') {
            $this->dispatch('show-toast', message: 'Cannot reply to a closed ticket!', type: 'error');

            return;
        }

        $this->validate();

        $attachmentPaths = [];

        if ($this->attachments) {
            foreach ($this->attachments as $attachment) {
                $path = $attachment->store('ticket-attachments', 'public');
                $attachmentPaths[] = [
                    'name' => $attachment->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $attachment->getMimeType(),
                    'size' => $attachment->getSize(),
                ];
            }
        }

        $userId = $this->senderId ?: Auth::id();

        TicketReply::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => $userId,
            'customer_name' => $this->ticket->customer_name,
            'message' => clean($this->message),
            'is_internal' => false,
            'is_technician' => false,
            'attachments' => empty($attachmentPaths) ? null : $attachmentPaths,
        ]);

        if (in_array($this->ticket->status, ['resolved'])) {
            $this->ticket->update(['status' => 'open']);
            $this->state = 'open';
        }

        $this->reset(['message', 'attachments']);
        $this->dispatch('resetEditor');
        $this->dispatch('show-toast', message: 'Reply added successfully!', type: 'success');
    }

    public function removeAttachment($index)
    {
        array_splice($this->attachments, $index, 1);
    }

    public function addInternalNote()
    {
        if ($this->ticket->status === 'closed') {
            $this->dispatch('show-toast', message: 'Cannot add notes to a closed ticket!', type: 'error');

            return;
        }

        $this->validate([
            'internalNote' => 'required|string|max:5000',
        ]);

        TicketReply::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'customer_name' => $this->ticket->customer_name,
            'message' => $this->internalNote,
            'is_internal' => true,
            'is_technician' => false,
            'attachments' => null,
        ]);

        $this->reset(['internalNote']);
        $this->dispatch('show-toast', message: 'Internal note added successfully!', type: 'success');
    }

    public function render()
    {
        return view('livewire.dashboard.ticket-details', [
            'ticket' => $this->ticket,
            'state' => $this->state,
            'agents' => $this->agents(),
            'replies' => TicketReply::where('ticket_id', $this->ticket->id)
                ->where('is_internal', false)
                ->with('user:id,name')
                ->orderBy('created_at', 'asc')
                ->get(),
            'internal_notes' => TicketReply::where('ticket_id', $this->ticket->id)
                ->where('is_internal', true)
                ->with('user:id,name')
                ->orderBy('created_at', 'asc')
                ->get(),
        ]);
    }
}
