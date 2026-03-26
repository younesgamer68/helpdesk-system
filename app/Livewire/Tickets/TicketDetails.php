<?php

namespace App\Livewire\Tickets;

use App\Ai\Agents\SupportReplyAgent;
use App\Mail\AgentRepliedToTicket;
use App\Mail\TicketClosed;
use App\Mail\TicketResolved;
use App\Models\AiSuggestionLog;
use App\Models\CompanyAiSettings;
use App\Models\KbArticle;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\TicketMention;
use App\Models\TicketReply;
use App\Models\User;
use App\Notifications\InternalNoteAdded;
use App\Notifications\TicketAssigned;
use App\Notifications\TicketPriorityChanged;
use App\Notifications\TicketReassigned;
use App\Notifications\TicketStatusChanged;
use App\Notifications\UserMentioned;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mews\Purifier\Facades\Purifier;

#[Layout('layouts.app.sidebar')]
class TicketDetails extends Component
{
    use WithFileUploads;

    public Ticket $ticket;

    public $state = '';

    public $senderId = null;

    public $keepOpen = false;

    public $aiSuggestion = '';

    public $aiTone = 'professional';

    public $showAiSuggestion = false;

    public $aiLoading = false;

    // AI Summary properties
    public $aiSummary = '';

    public $summaryLoading = false;

    public $showSummary = false;

    public $agentSearch = '';

    public string $kbSearch = '';

    public ?int $pendingAssignAgentId = null;

    public bool $showTeamPickerModal = false;

    public bool $showActionConfirmationModal = false;

    public string $confirmationTitle = 'Confirm action';

    public string $confirmationMessage = 'Are you sure you want to continue?';

    public string $confirmationButtonLabel = 'Confirm';

    public string $confirmationButtonStyle = 'confirm';

    public ?string $pendingConfirmationAction = null;

    public ?string $pendingConfirmationValue = null;

    #[Validate('required|string|max:5000')]
    public $message = '';

    public $internalNote = '';

    public array $mentionedUserIds = [];

    #[Validate(['attachments.*' => 'nullable|file|max:10240'])] // 10MB Max for admins
    public $attachments = [];

    public function mount(Ticket $ticket)
    {
        $this->ticket = $ticket->load([
            'assignedTo:id,name,email',
            'category:id,name,description',
            'team:id,name,color',
            'replies.user',
            'customer:id,name,email,phone',
            'company:id,name,slug',
            'company.slaPolicy:id,company_id,is_enabled',
        ]);
        $this->state = $ticket->status;

        // Redirect outsider operators (inline check — computed props may not be available in mount)
        if (Auth::user()->isOperator()) {
            $isAssignee = $this->ticket->assigned_to === Auth::id();
            $isTeammate = ! $isAssignee
                && $this->ticket->team_id !== null
                && Auth::user()->teams()->pluck('teams.id')->contains($this->ticket->team_id);

            if (! $isAssignee && ! $isTeammate) {
                session()->flash('error', 'You do not have access to this ticket.');
                $this->redirect(route('tickets', ['company' => $ticket->company->slug]));

                return;
            }
        }
    }

    #[Computed]
    public function agents(): \Illuminate\Database\Eloquent\Collection
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = User::query()
            ->where('company_id', '=', $user->company_id)
            ->select('id', 'name', 'email')
            ->with('teams:id,name,color')
            ->orderBy('name', 'asc');

        if (! empty($this->agentSearch)) {
            $query->where('name', 'like', '%'.$this->agentSearch.'%');
        }

        return $query->get(['id', 'name', 'email']);
    }

    #[Computed]
    public function teamsForAssign()
    {
        return Team::query()
            ->where('company_id', Auth::user()->company_id)
            ->select('id', 'name', 'color')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function pendingAgentTeams(): \Illuminate\Database\Eloquent\Collection
    {
        if (! $this->pendingAssignAgentId) {
            return new \Illuminate\Database\Eloquent\Collection;
        }

        $agent = User::query()->find($this->pendingAssignAgentId);

        return $agent ? $agent->teams()->select('teams.id', 'teams.name', 'teams.color')->get() : new \Illuminate\Database\Eloquent\Collection;
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{id: int, title: string, slug: string}>
     */
    #[Computed]
    public function kbResults(): \Illuminate\Support\Collection
    {
        if (strlen($this->kbSearch) < 2) {
            return collect();
        }

        return KbArticle::query()
            ->where('status', 'published')
            ->where('title', 'like', '%'.$this->kbSearch.'%')
            ->select('id', 'title', 'slug')
            ->limit(6)
            ->get()
            ->map(fn (KbArticle $article) => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
            ]);
    }

    public function insertKbArticle(string $slug, string $title): void
    {
        $url = route('kb.public.article', [
            'company' => $this->ticket->company->slug,
            'article' => $slug,
        ]);

        $this->dispatch('kb-insert', url: $url, title: $title);
        $this->kbSearch = '';
    }

    #[Computed]
    public function aiSettings(): CompanyAiSettings
    {
        return CompanyAiSettings::query()->firstOrCreate(
            ['company_id' => Auth::user()->company_id],
            [
                'ai_suggestions_enabled' => false,
                'ai_summary_enabled' => false,
                'ai_chatbot_enabled' => false,
                'ai_model' => 'gemini-2.5-flash',
            ]
        );
    }

    #[Computed]
    public function replies(): \Illuminate\Database\Eloquent\Collection
    {
        return TicketReply::query()
            ->where('ticket_id', $this->ticket->id)
            ->where('is_internal', false)
            ->with('user:id,name')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    #[Computed]
    public function internalNotes(): \Illuminate\Database\Eloquent\Collection
    {
        return TicketReply::query()
            ->where('ticket_id', $this->ticket->id)
            ->where('is_internal', true)
            ->with('user:id,name')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    #[Computed]
    public function ticketLogs(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->ticket->logs()
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    #[Computed]
    public function isAssignee(): bool
    {
        return Auth::user()->isOperator() && $this->ticket->assigned_to === Auth::id();
    }

    #[Computed]
    public function isTeammate(): bool
    {
        if (! Auth::user()->isOperator()) {
            return false;
        }

        if ($this->ticket->assigned_to === Auth::id()) {
            return false;
        }

        if ($this->ticket->team_id === null) {
            return false;
        }

        return Auth::user()->teams()->pluck('teams.id')->contains($this->ticket->team_id);
    }

    #[Computed]
    public function isOutsider(): bool
    {
        return Auth::user()->isOperator() && ! $this->isAssignee && ! $this->isTeammate;
    }

    private function logAction($action, $description)
    {
        TicketLog::create([
            'company_id' => $this->ticket->company_id,
            'ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
        ]);

        unset($this->ticketLogs);
    }

    private function logSuggestionAction(string $action, ?string $text = null): void
    {
        AiSuggestionLog::create([
            'company_id' => $this->ticket->company_id,
            'ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'suggestion_text' => $text,
        ]);
    }

    public function resolve()
    {
        if ($this->ticket->status === 'resolved') {
            $this->dispatch('show-toast', message: 'Ticket marked as resolved!', type: 'success');

            return;
        }

        $oldStatus = $this->ticket->status;

        $this->ticket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        $this->ticket->refresh();
        $this->state = $this->ticket->status;

        if ($this->ticket->assignedTo && $this->ticket->assignedTo->id !== Auth::id()) {
            $this->ticket->assignedTo->notify(new TicketStatusChanged($this->ticket, str_replace('_', ' ', ucfirst($oldStatus)), 'Resolved'));
        }

        $customerEmail = $this->ticket->customer_email;

        $this->logAction('resolved', 'Ticket resolved.');

        $this->dispatch('show-toast', message: 'Ticket marked as resolved!', type: 'success');

        if ($customerEmail) {
            if (! $this->ticket->tracking_token) {
                $this->ticket->update(['tracking_token' => Str::random(32)]);
            }

            Mail::to($customerEmail)->queue(new TicketResolved($this->ticket));
        }
    }

    public function unresolve()
    {
        if ($this->ticket->status !== 'resolved') {
            $this->dispatch('show-toast', message: 'Ticket reopened!', type: 'success');

            return;
        }

        $oldStatus = $this->ticket->status;

        $this->ticket->update([
            'status' => 'open',
            'resolved_at' => null,
            'warning_sent_at' => null,
        ]);

        $this->ticket->refresh();
        $this->state = $this->ticket->status;

        if ($this->ticket->assignedTo && $this->ticket->assignedTo->id !== Auth::id()) {
            $this->ticket->assignedTo->notify(new TicketStatusChanged($this->ticket, 'Resolved', 'Open'));
        }

        $this->logAction('status_changed', 'Ticket unresolved.');

        $this->dispatch('show-toast', message: 'Ticket reopened!', type: 'success');
    }

    public function assign($agentId)
    {
        if (Auth::user()->isOperator()) {
            $this->dispatch('show-toast', message: 'Unauthorized. Only admins can assign tickets.', type: 'error');

            return;
        }

        $agent = $agentId !== null ? $this->agents()->where('id', '=', $agentId)->first() : null;

        if ($agentId !== null && $agent === null) {
            $this->dispatch('show-toast', message: 'Invalid agent selected.', type: 'error');

            return;
        }

        if ($this->ticket->assigned_to === $agentId) {
            $message = $agentId === null
                ? 'Ticket is already unassigned!'
                : "Ticket is already assigned to {$agent->name}!";
            $this->dispatch('show-toast', message: $message, type: 'error');

            return;
        }

        $oldAgentId = $this->ticket->assigned_to;

        $teamId = null;
        if ($agentId !== null) {
            $agentTeams = $agent->teams()->pluck('teams.id');
            if ($agentTeams->count() === 1) {
                $teamId = $agentTeams->first();
            } elseif ($agentTeams->count() > 1) {
                // If ticket already has a team the agent belongs to, keep it
                if ($this->ticket->team_id && $agentTeams->contains($this->ticket->team_id)) {
                    $teamId = $this->ticket->team_id;
                } else {
                    // Show team picker modal
                    $this->pendingAssignAgentId = $agentId;
                    $this->showTeamPickerModal = true;

                    return;
                }
            }
        }

        $this->performAssignment($agentId, $teamId);
    }

    public function confirmAssignWithTeam(int $teamId): void
    {
        $agentId = $this->pendingAssignAgentId;
        $this->showTeamPickerModal = false;
        $this->pendingAssignAgentId = null;

        if ($agentId === null) {
            return;
        }

        $agent = $this->agents()->where('id', '=', $agentId)->first();
        if (! $agent) {
            return;
        }

        // Validate the team belongs to this agent
        $agentTeams = $agent->teams()->pluck('teams.id');
        if (! $agentTeams->contains($teamId)) {
            $this->dispatch('show-toast', message: 'Agent does not belong to that team.', type: 'error');

            return;
        }

        $this->performAssignment($agentId, $teamId);
    }

    public function cancelAssign(): void
    {
        $this->showTeamPickerModal = false;
        $this->pendingAssignAgentId = null;
    }

    private function performAssignment(?int $agentId, ?int $teamId): void
    {
        $agent = $agentId !== null ? $this->agents()->where('id', '=', $agentId)->first() : null;
        $oldAgentId = $this->ticket->assigned_to;

        // Decrement old agent counter
        if ($this->ticket->assigned_to) {
            $oldAgent = User::find($this->ticket->assigned_to);
            if ($oldAgent && $oldAgent->assigned_tickets_count > 0
                && ! in_array($this->ticket->status, ['resolved', 'closed'])) {
                $oldAgent->decrement('assigned_tickets_count');
            }
        }

        // Update ticket
        $this->ticket->update([
            'assigned_to' => $agentId,
            'team_id' => $agentId === null ? null : $teamId,
        ]);

        // Increment new agent counter and update last_assigned_at
        if ($agentId !== null && $agent
            && ! in_array($this->ticket->status, ['resolved', 'closed'])) {
            $agent->increment('assigned_tickets_count');
            $agent->update(['last_assigned_at' => now()]);
        }

        $this->ticket->refresh();
        $this->ticket->load(['assignedTo', 'team:id,name,color']);

        if ($agentId !== null && $agentId !== Auth::id() && $agent) {
            $agent->notify(new TicketAssigned($this->ticket));
        }

        if ($oldAgentId && $oldAgentId !== $agentId) {
            /** @var \App\Models\User|null $oldAgent */
            $oldAgent = User::query()->find($oldAgentId, ['id', 'name', 'email']);
            if ($oldAgent) {
                $oldAgent->notify(new TicketReassigned($this->ticket));
            }
        }

        $this->logAction('assigned', $agentId === null ? 'Unassigned.' : "Assigned to {$agent->name}.");

        $this->dispatch('show-toast', message: $agentId === null ? 'Ticket unassigned!' : "Ticket assigned to {$agent->name}!", type: 'success');
    }

    public function assignToTeam(?int $teamId): void
    {
        if ($this->ticket->team_id === $teamId) {
            return;
        }

        $this->ticket->update(['team_id' => $teamId]);
        $this->ticket->refresh();
        $this->ticket->load('team:id,name,color');

        $teamName = $teamId ? $this->ticket->team?->name : null;
        $this->logAction('team_assigned', $teamId ? "Assigned to team {$teamName}." : 'Removed from team.');
        $this->dispatch('show-toast', message: $teamId ? "Assigned to team {$teamName}!" : 'Removed from team.', type: 'success');
    }

    public function changePriority($priority)
    {
        if (Auth::user()->isOperator() && ! $this->isAssignee) {
            $this->dispatch('show-toast', message: 'You are not authorized to change the priority.', type: 'error');

            return;
        }

        $normalizedPriority = strtolower($priority);

        if ($this->ticket->priority === $normalizedPriority) {
            $this->dispatch('show-toast', message: "Ticket is already prioritized as {$priority}!", type: 'error');

            return;
        }

        if (! in_array($normalizedPriority, ['low', 'medium', 'high', 'urgent'])) {
            $this->dispatch('show-toast', message: 'Invalid priority level!', type: 'error');

            return;
        }

        $oldPriority = $this->ticket->priority;
        $this->ticket->update(['priority' => $normalizedPriority]);
        $this->ticket->refresh();

        if ($this->ticket->assignedTo && $this->ticket->assigned_to !== Auth::id()) {
            $this->ticket->assignedTo->notify(new TicketPriorityChanged($this->ticket, ucfirst($oldPriority), ucfirst($normalizedPriority)));
        }

        $this->logAction('priority_changed', 'Priority changed to '.ucfirst($normalizedPriority).'.');

        $this->dispatch('show-toast', message: 'Priority changed to '.ucfirst($normalizedPriority).'!', type: 'success');
    }

    public function changeStatus($status)
    {
        if (Auth::user()->isOperator() && ! $this->isAssignee) {
            $this->dispatch('show-toast', message: 'You are not authorized to change the status.', type: 'error');

            return;
        }

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

        $oldStatus = $this->ticket->status;
        $this->ticket->update(['status' => $dbStatus]);
        $this->ticket->refresh();
        $this->state = $this->ticket->status;

        if ($this->ticket->assignedTo && $this->ticket->assignedTo->id !== Auth::id()) {
            $this->ticket->assignedTo->notify(new TicketStatusChanged($this->ticket, str_replace('_', ' ', ucfirst($oldStatus)), str_replace('_', ' ', ucfirst($dbStatus))));
        }

        $this->logAction('status_changed', 'Status changed to '.str_replace('_', ' ', ucfirst($dbStatus)).'.');

        $this->dispatch('show-toast', message: 'Status changed to '.str_replace('_', ' ', ucfirst($dbStatus)).'!', type: 'success');
    }

    public function closeTicket()
    {
        if (Auth::user()->isOperator()) {
            $this->dispatch('show-toast', message: 'Unauthorized. Only admins can close tickets.', type: 'error');

            return;
        }

        if ($this->ticket->status === 'closed') {
            $this->dispatch('show-toast', message: 'Ticket is already closed!', type: 'error');

            return;
        }

        $oldStatus = $this->ticket->status;
        $this->ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
            'close_reason' => 'manual',
        ]);

        $this->ticket->refresh();
        $this->state = $this->ticket->status;

        if ($this->ticket->assignedTo && $this->ticket->assignedTo->id !== Auth::id()) {
            $this->ticket->assignedTo->notify(new TicketStatusChanged($this->ticket, str_replace('_', ' ', ucfirst($oldStatus)), 'Closed'));
        }

        $customerEmail = $this->ticket->customer_email;

        if ($customerEmail) {
            if (! $this->ticket->tracking_token) {
                $this->ticket->update(['tracking_token' => Str::random(32)]);
            }

            Mail::to($customerEmail)->send(new TicketClosed($this->ticket, 'manual'));
        }

        $this->logAction('manually_closed', 'Ticket closed manually.');

        $this->dispatch('show-toast', message: 'Ticket closed successfully!', type: 'success');
    }

    public function promptActionConfirmation(string $action, ?string $value = null): void
    {
        $actionConfig = match ($action) {
            'resolve' => [
                'title' => 'Mark As Resolved',
                'message' => 'Confirm marking this ticket as resolved.',
                'button' => 'Yes, resolve',
                'style' => 'confirm',
            ],
            'unresolve' => [
                'title' => 'Reopen Ticket',
                'message' => 'Confirm reopening this ticket.',
                'button' => 'Yes, reopen',
                'style' => 'confirm',
            ],
            'unassign' => [
                'title' => 'Unassign Ticket',
                'message' => 'Remove the current assignee from this ticket?',
                'button' => 'Yes, unassign',
                'style' => 'danger',
            ],
            'priority' => [
                'title' => 'Change Priority',
                'message' => 'Set ticket priority to '.ucfirst((string) $value).'?',
                'button' => 'Yes, update priority',
                'style' => 'confirm',
            ],
            'status' => [
                'title' => 'Change Status',
                'message' => 'Set ticket status to '.str_replace('_', ' ', (string) $value).'?',
                'button' => 'Yes, update status',
                'style' => (string) $value === 'closed' ? 'danger' : 'confirm',
            ],
            'close' => [
                'title' => 'Close Ticket',
                'message' => 'Close this ticket now? This is a destructive action.',
                'button' => 'Yes, close ticket',
                'style' => 'danger',
            ],
            default => [
                'title' => 'Confirm action',
                'message' => 'Are you sure you want to continue?',
                'button' => 'Confirm',
                'style' => 'confirm',
            ],
        };

        $this->pendingConfirmationAction = $action;
        $this->pendingConfirmationValue = $value;
        $this->confirmationTitle = $actionConfig['title'];
        $this->confirmationMessage = $actionConfig['message'];
        $this->confirmationButtonLabel = $actionConfig['button'];
        $this->confirmationButtonStyle = $actionConfig['style'];
        $this->showActionConfirmationModal = true;
    }

    public function cancelActionConfirmation(): void
    {
        $this->showActionConfirmationModal = false;
        $this->pendingConfirmationAction = null;
        $this->pendingConfirmationValue = null;
    }

    public function confirmActionConfirmation(): void
    {
        $action = $this->pendingConfirmationAction;
        $value = $this->pendingConfirmationValue;

        $this->cancelActionConfirmation();

        match ($action) {
            'resolve' => $this->resolve(),
            'unresolve' => $this->unresolve(),
            'unassign' => $this->assign(null),
            'priority' => $this->changePriority((string) $value),
            'status' => $this->changeStatus((string) $value),
            'close' => $this->closeTicket(),
            default => null,
        };
    }

    public function addReply()
    {
        if (Auth::user()->isOperator() && ! $this->isAssignee) {
            $this->dispatch('show-toast', message: 'Only the assigned agent or an admin can reply to tickets.', type: 'error');

            return;
        }

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

        $userId = Auth::id();
        if (Auth::user()->isOperator()) {
            $this->senderId = null;
        }
        if ($this->senderId) {
            $validSender = \App\Models\User::where('id', $this->senderId)
                ->where('company_id', Auth::user()->company_id)
                ->exists();
            if ($validSender) {
                $userId = $this->senderId;
            } else {
                $this->senderId = null;
            }
        }

        $reply = TicketReply::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => $userId,
            'customer_name' => $this->ticket->customer?->name ?? $this->ticket->getCustomerNameAttribute(),
            'message' => Purifier::clean($this->message),
            'is_internal' => false,
            'is_technician' => false,
            'attachments' => empty($attachmentPaths) ? null : $attachmentPaths,
        ]);

        // Notify customer with tracking link when agent replies to a verified ticket
        if ($this->ticket->verified && $this->ticket->tracking_token && $this->ticket->customer?->email) {
            $this->ticket->load('company');
            Mail::to($this->ticket->customer?->email)->send(new AgentRepliedToTicket($this->ticket, $reply));
        }

        // TRIGGER 2: Agent sends a reply
        if ($this->ticket->status !== 'closed' && ! $this->keepOpen) {
            $this->ticket->update(['status' => 'pending']);
            $this->state = 'pending';
        }

        $this->reset(['message', 'attachments']);
        $this->clearAgentTypingState();
        $this->dispatch('resetEditor');

        // Notify assigned agent about the new reply if they aren't the sender
        if ($this->ticket->assigned_to && $this->ticket->assigned_to !== Auth::id() && $this->ticket->assignedTo) {
            $this->ticket->assignedTo->notify(new \App\Notifications\ClientReplied($this->ticket));
        }

        $this->logAction('reply_added', 'Added a reply. Status set to pending.');

        $this->dispatch('show-toast', message: 'Reply added successfully!', type: 'success');
    }

    public function updatedMessage($value): void
    {
        $hasMessage = trim((string) $value) !== '';

        if ($hasMessage) {
            Cache::put($this->agentTypingCacheKey(), Auth::user()->name, now()->addSeconds(6));

            return;
        }

        $this->clearAgentTypingState();
    }

    private function agentTypingCacheKey(): string
    {
        return 'ticket:typing:agent:'.$this->ticket->id;
    }

    private function clearAgentTypingState(): void
    {
        Cache::forget($this->agentTypingCacheKey());
    }

    #[Computed]
    public function isCustomerTyping(): bool
    {
        return (bool) Cache::get('ticket:typing:customer:'.$this->ticket->id, false);
    }

    public function startAiSuggestion()
    {
        if ($this->ticket->status === 'closed') {
            return;
        }

        if (! $this->aiSettings->ai_suggestions_enabled) {
            $this->dispatch('show-toast', message: 'AI suggestions are disabled in settings.', type: 'error');

            return;
        }

        $this->showAiSuggestion = true;
        $this->aiLoading = true;
        $this->aiSuggestion = '';
        $this->js('$wire.generateAiSuggestion()');
    }

    public function generateAiSuggestion()
    {
        if ($this->ticket->status === 'closed') {
            return;
        }

        $settings = $this->aiSettings;

        if (! $settings->ai_suggestions_enabled) {
            return;
        }

        $this->aiLoading = true;
        $this->showAiSuggestion = true;

        $context = 'Company name: '.Auth::user()->company->name."\n";
        $context .= 'Ticket category: '.($this->ticket->category->name ?? 'None')."\n";
        $context .= 'Ticket priority: '.$this->ticket->priority."\n";
        $context .= 'Customer name: '.($this->ticket->customer?->name ?? 'Unknown')."\n";
        $context .= "Original ticket description:\n".$this->ticket->description."\n\n";
        $context .= "Full conversation history:\n";

        foreach ($this->ticket->replies as $reply) {
            $sender = $reply->user_id ? 'Agent' : 'Customer';
            $context .= "{$sender}: ".strip_tags($reply->message)."\n";
        }

        $context .= "\n\nPlease rewrite the suggested reply according to this tone:\n";
        switch ($this->aiTone) {
            case 'friendly':
                $context .= 'Reply in a warm, friendly and approachable tone.';
                break;
            case 'formal':
                $context .= 'Reply in a formal and official tone.';
                break;
            case 'professional':
            default:
                $context .= 'Reply in a professional and clear tone.';
                break;
        }

        try {
            $agent = new SupportReplyAgent;
            $result = $agent->prompt(
                $context,
                provider: $settings->resolveProvider(),
                model: $settings->ai_model,
            );

            $this->aiSuggestion = (string) $result;
        } catch (\Exception $e) {
            $errorMsg = str_contains(strtolower($e->getMessage()), 'rate limit')
                ? 'AI provider rate limit reached. Please wait a moment and try again.'
                : 'Failed to generate suggestion: '.$e->getMessage();
            $this->aiSuggestion = $errorMsg;
            $this->dispatch('show-toast', message: $errorMsg, type: 'error');
        }

        $this->logSuggestionAction('generate', $this->aiSuggestion);
        $this->aiLoading = false;
    }

    public function regenerateWithTone($tone)
    {
        if ($this->ticket->status === 'closed') {
            return;
        }

        $this->aiTone = $tone;
        $this->aiLoading = true;
        $this->logSuggestionAction('regenerate');
        $this->js('$wire.generateAiSuggestion()');
    }

    // AI Summary methods
    public function generateAiSummary()
    {
        $settings = $this->aiSettings;

        if (! $settings->ai_summary_enabled) {
            $this->summaryLoading = false;
            $this->showSummary = false;
            $this->dispatch('show-toast', message: 'AI summary is disabled in settings.', type: 'error');

            return;
        }

        $this->summaryLoading = true;

        // Load replies with user relationship to avoid lazy loading
        $replies = $this->ticket->replies()->with('user:id,name')->orderBy('created_at', 'desc')->get();
        $replyCount = $replies->count();

        $prompt = "You are a helpdesk assistant summarizing a support ticket for an agent.\n\n";
        $prompt .= "Ticket Information:\n";
        $prompt .= '- Subject: '.$this->ticket->subject."\n";
        $prompt .= '- Customer: '.($this->ticket->customer->name ?? $this->ticket->customer_name ?? 'Unknown')."\n";
        $prompt .= '- Category: '.($this->ticket->category->name ?? 'None')."\n";
        $prompt .= '- Priority: '.$this->ticket->priority."\n";
        $prompt .= '- Status: '.str_replace('_', ' ', ucfirst($this->ticket->status))."\n\n";
        $prompt .= "Original Description:\n".$this->ticket->description."\n\n";

        if ($replyCount > 0) {
            $prompt .= "Reply History (most recent first):\n";
            foreach ($replies as $reply) {
                // Safely access user without triggering lazy loading
                $senderName = null;
                if ($reply->user_id && $reply->relationLoaded('user') && $reply->user) {
                    $senderName = $reply->user->name;
                }
                $sender = $reply->is_internal ? 'Internal Note' : ($reply->user_id ? ($senderName ?? 'Agent') : ($reply->customer_name ?? 'Customer'));
                $senderType = $reply->is_internal ? 'Note' : ($reply->user_id ? 'Agent' : 'Customer');
                $prompt .= "[{$senderType}: {$sender}]: ".strip_tags($reply->message)."\n";
            }
        } else {
            $prompt .= "No replies yet.\n";
        }

        $prompt .= "\n\nPlease provide a concise summary covering exactly three points:\n";
        $prompt .= "1. Issue: What is the customer asking about? (1-2 sentences)\n";
        $prompt .= "2. Progress: What has been tried or discussed so far? (1-2 sentences)\n";
        $prompt .= "3. Next Step: What needs to happen next to resolve this ticket? (1-2 sentences)\n\n";
        $prompt .= "Format your response exactly as:\n";
        $prompt .= "Issue: <text>\n";
        $prompt .= "Progress: <text>\n";
        $prompt .= "Next Step: <text>\n";
        $prompt .= 'Do not include any preamble or extra text.';

        try {
            $agent = new SupportReplyAgent;
            $result = $agent->prompt(
                $prompt,
                provider: $settings->resolveProvider(),
                model: $settings->ai_model,
            );
            $this->aiSummary = (string) $result;
            $this->dispatch('show-toast', message: 'AI summary generated.', type: 'success');
        } catch (\Exception $e) {
            $summaryError = str_contains(strtolower($e->getMessage()), 'rate limit')
                ? 'AI provider rate limit reached. Please wait a moment and try again.'
                : 'Failed to generate AI summary.';
            $this->aiSummary = 'Issue: Unable to generate summary.\nProgress: -\nNext Step: -';
            $this->dispatch('show-toast', message: $summaryError, type: 'error');
        }

        $this->summaryLoading = false;
    }

    public function toggleSummary()
    {
        $this->showSummary = ! $this->showSummary;
    }

    public function regenerateSummary()
    {
        $this->aiSummary = '';
        $this->summaryLoading = true;
        // Dispatch event to clear displayedSummary in Alpine
        $this->dispatch('clearSummaryDisplay');
        // Defer to let Alpine pick up the summaryLoading = true state,
        // and then generate the summary.
        $this->js('$wire.generateAiSummary()');
    }

    public function useAiSuggestion(?string $content = null)
    {
        $suggestionContent = trim((string) ($content ?? $this->aiSuggestion));

        if ($suggestionContent === '') {
            return;
        }

        $this->logSuggestionAction('use', $suggestionContent);
        $this->dispatch('loadAiSuggestion', content: $suggestionContent);
        $this->showAiSuggestion = false;
        $this->dispatch('show-toast', message: 'AI suggestion applied to reply.', type: 'success');
    }

    public function dismissAiSuggestion()
    {
        $this->logSuggestionAction('dismiss');
        $this->showAiSuggestion = false;
        $this->aiSuggestion = '';
    }

    public function removeAttachment($index)
    {
        array_splice($this->attachments, $index, 1);
    }

    #[Computed]
    public function availableTeammates(): array
    {
        $query = User::query()
            ->where('id', '!=', Auth::id())
            ->where('company_id', Auth::user()->company_id);

        if (Auth::user()->isOperator()) {
            $teamIds = Auth::user()->teams()->pluck('teams.id');

            if ($teamIds->isEmpty()) {
                return [];
            }

            $query->where(function ($q) use ($teamIds) {
                $q->whereHas('teams', fn ($sub) => $sub->whereIn('teams.id', $teamIds))
                    ->orWhere('role', 'admin');
            });
        }

        return $query->limit(50)
            ->get(['id', 'name'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'initials' => $user->initials(),
            ])->toArray();
    }

    public function addMentionedUser(int $userId): void
    {
        $user = User::where('id', $userId)
            ->where('company_id', Auth::user()->company_id)
            ->first();

        if (! $user) {
            return;
        }

        if (! in_array($userId, $this->mentionedUserIds)) {
            $this->mentionedUserIds[] = $userId;
        }

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

        $reply = TicketReply::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'customer_name' => $this->ticket->customer?->name ?? '',
            'message' => Purifier::clean($this->internalNote),
            'is_internal' => true,
            'is_technician' => false,
            'attachments' => null,
        ]);

        foreach ($this->mentionedUserIds as $mentionedId) {
            $mentionedUser = User::where('id', $mentionedId)
                ->where('company_id', Auth::user()->company_id)
                ->first();

            if (! $mentionedUser) {
                continue;
            }

            TicketMention::create([
                'ticket_id' => $this->ticket->id,
                'ticket_reply_id' => $reply->id,
                'mentioned_user_id' => $mentionedId,
                'mentioned_by_user_id' => Auth::id(),
                'company_id' => Auth::user()->company_id,
            ]);

            $mentionedUser->notify(new UserMentioned(
                $this->ticket, $reply, Auth::user()
            ));
        }

        $this->mentionedUserIds = [];

        $this->reset(['internalNote']);

        // Notify admins and assigned agent about internal note
        $recipients = User::where('company_id', $this->ticket->company_id)
            ->where('id', '!=', Auth::id())
            ->where(function ($query) {
                $query->where('role', 'admin')
                    ->orWhere('id', $this->ticket->assigned_to);
            })
            ->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(new InternalNoteAdded($this->ticket));
        }

        $this->dispatch('show-toast', message: 'Internal note added successfully!', type: 'success');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.tickets.ticket-details', [
            'ticket' => $this->ticket,
            'state' => $this->state,
            'agents' => $this->agents(),
            'teams' => $this->teamsForAssign(),
            'isCustomerTyping' => $this->isCustomerTyping,
        ]);
    }
}
