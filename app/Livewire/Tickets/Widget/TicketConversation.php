<?php

namespace App\Livewire\Tickets\Widget;

use App\Events\NewTicketReply;
use App\Events\TicketTypingUpdated;
use App\Mail\TicketVerified;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Notifications\ClientReplied;
use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mews\Purifier\Facades\Purifier;

class TicketConversation extends Component
{
    use WithFileUploads;

    public Ticket $ticket;

    #[Validate('required|string|max:500')]
    public $message = '';

    #[Validate(['attachments.*' => 'nullable|file|max:2048'])] // 2MB Max
    #[Validate('max:2', message: 'You can only upload a maximum of 2 files.')]
    public $attachments = [];

    public bool $confirmLinkedTicket = false;

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    public function getListeners(): array
    {
        return [
            "echo:ticket.{$this->ticket->id},.NewTicketReply" => 'refreshConversation',
            "echo:ticket.{$this->ticket->id},.TicketTypingUpdated" => 'refreshTyping',
        ];
    }

    public function refreshConversation(): void
    {
        // Re-render triggers fresh data fetch in render()
    }

    public function refreshTyping(): void
    {
        // Re-render picks up latest typing state from cache
    }

    #[Computed]
    public function slaPolicy(): ?SlaPolicy
    {
        return SlaPolicy::withoutGlobalScope(CompanyScope::class)
            ->where('company_id', $this->ticket->company_id)
            ->first();
    }

    public function submitReply(): void
    {
        $status = $this->ticket->status;

        if ($status === 'closed') {
            $linkedTicketDays = $this->slaPolicy?->linked_ticket_days ?? 7;
            $closedAt = $this->ticket->closed_at;

            if (! $closedAt || now()->diffInDays($closedAt, false) < -$linkedTicketDays) {
                $this->dispatch('show-toast', message: 'This ticket is permanently closed. Please submit a new support request.', type: 'error');

                return;
            }

            if (! $this->confirmLinkedTicket) {
                $this->confirmLinkedTicket = true;

                return;
            }

            $this->validate();
            $this->createLinkedTicket();

            return;
        }

        if ($status === 'resolved') {
            $reopenHours = $this->slaPolicy?->reopen_hours ?? 48;
            $resolvedAt = $this->ticket->resolved_at;

            if ($resolvedAt && now()->diffInHours($resolvedAt, false) < -$reopenHours) {
                $this->dispatch('show-toast', message: 'The reopen window has passed. Please submit a new support request.', type: 'error');

                return;
            }

            $this->validate();
            $this->createReply();
            $this->ticket->update([
                'status' => 'open',
                'resolved_at' => null,
                'warning_sent_at' => null,
            ]);

            return;
        }

        $this->validate();
        $this->createReply();

        if ($this->ticket->status === 'pending') {
            $this->ticket->update(['status' => 'in_progress']);
        }
    }

    public function cancelLinkedTicket(): void
    {
        $this->confirmLinkedTicket = false;
    }

    public function markTyping(): void
    {
        $this->setTypingState('customer', true);
        broadcast(new TicketTypingUpdated($this->ticket->id))->toOthers();
    }

    private function createReply(): void
    {
        $attachmentPaths = $this->storeAttachments();

        TicketReply::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => null,
            'customer_name' => $this->ticket->customer?->name ?? '',
            'message' => Purifier::clean($this->message),
            'is_internal' => false,
            'attachments' => empty($attachmentPaths) ? null : $attachmentPaths,
        ]);

        $this->ticket->refresh();

        if ($this->ticket->assignedTo) {
            $this->ticket->assignedTo->notify(new ClientReplied($this->ticket));
        }

        $this->reset(['message', 'attachments']);
        $this->setTypingState('customer', false);
        $this->dispatch('resetEditor');

        broadcast(new NewTicketReply($this->ticket->id))->toOthers();

        session()->flash('success', 'Your reply has been submitted!');
    }

    private function createLinkedTicket(): void
    {
        $attachmentPaths = $this->storeAttachments();

        do {
            $ticketNumber = 'TKT-'.strtoupper(Str::random(6));
        } while (Ticket::where('ticket_number', $ticketNumber)->exists());

        $linkedTicket = Ticket::create([
            'company_id' => $this->ticket->company_id,
            'customer_id' => $this->ticket->customer_id,
            'assigned_to' => $this->ticket->assigned_to,
            'category_id' => $this->ticket->category_id,
            'subject' => 'Follow-up: '.$this->ticket->subject,
            'description' => Purifier::clean($this->message),
            'status' => 'open',
            'priority' => $this->ticket->priority,
            'parent_ticket_id' => $this->ticket->id,
            'ticket_number' => $ticketNumber,
            'tracking_token' => Str::random(32),
            'verified' => true,
            'source' => $this->ticket->source ?? 'widget',
        ]);

        if (! empty($attachmentPaths)) {
            TicketReply::create([
                'ticket_id' => $linkedTicket->id,
                'user_id' => null,
                'customer_name' => $this->ticket->customer?->name ?? '',
                'message' => Purifier::clean($this->message),
                'is_internal' => false,
                'attachments' => $attachmentPaths,
            ]);
        }

        if ($linkedTicket->assignedTo) {
            $linkedTicket->assignedTo->notify(new ClientReplied($linkedTicket));
        }

        if ($linkedTicket->customer_email) {
            Mail::to($linkedTicket->customer_email)->send(new TicketVerified($linkedTicket, $linkedTicket->tracking_token));
        }

        $this->confirmLinkedTicket = false;
        $this->reset(['message', 'attachments']);
        $this->setTypingState('customer', false);
        $this->dispatch('resetEditor');

        session()->flash('success', 'A new follow-up ticket (#'.$linkedTicket->ticket_number.') has been created!');
    }

    /**
     * @return array<int, array{name: string, path: string, mime_type: string, size: int}>
     */
    private function storeAttachments(): array
    {
        $paths = [];

        foreach ($this->attachments as $attachment) {
            $path = $attachment->store('ticket-attachments', 'public');
            $paths[] = [
                'name' => $attachment->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $attachment->getMimeType(),
                'size' => $attachment->getSize(),
            ];
        }

        return $paths;
    }

    public function removeAttachment(int $index): void
    {
        array_splice($this->attachments, $index, 1);
    }

    private function typingCacheKey(string $actor): string
    {
        return 'ticket:typing:'.$actor.':'.$this->ticket->id;
    }

    private function setTypingState(string $actor, bool $isTyping): void
    {
        $key = $this->typingCacheKey($actor);

        if ($isTyping) {
            Cache::put($key, true, now()->addSeconds(6));

            return;
        }

        Cache::forget($key);
    }

    private function isActorTyping(string $actor): bool
    {
        return (bool) Cache::get($this->typingCacheKey($actor), false);
    }

    private function getAgentTypingName(): ?string
    {
        $value = Cache::get($this->typingCacheKey('agent'));

        return is_string($value) ? $value : ($value ? 'Support team' : null);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.tickets.widget.ticket-conversation', [
            'replies' => TicketReply::where('ticket_id', $this->ticket->id)
                ->where('is_internal', false)
                ->with('user:id,name')
                ->orderBy('created_at', 'asc')
                ->get(),
            'supportTypingName' => $this->getAgentTypingName(),
        ]);
    }
}
