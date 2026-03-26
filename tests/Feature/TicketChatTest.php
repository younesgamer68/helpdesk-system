<?php

use App\Livewire\Dashboard\TicketDetails;
use App\Livewire\Widget\TicketConversation;
use App\Mail\AgentRepliedToTicket;
use App\Mail\TicketVerified;
use App\Models\Company;
use App\Models\Customer;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id, 'role' => 'admin']);
    $this->category = TicketCategory::factory()->create(['company_id' => $this->company->id]);

    $this->customer = Customer::create([
        'company_id' => $this->company->id,
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $this->ticket = Ticket::create([
        'company_id' => $this->company->id,
        'ticket_number' => 'TKT-TEST1234',
        'customer_id' => $this->customer->id,
        'subject' => 'Test Subject',
        'description' => 'Test Description',
        'category_id' => $this->category->id,
        'priority' => 'low',
        'status' => 'open',
        'verified' => true,
        'verification_token' => 'test-token',
        'tracking_token' => 'test-tracking-token',
    ]);
});

it('allows customer to send a reply', function () {
    Livewire::test(TicketConversation::class, ['ticket' => $this->ticket])
        ->set('message', 'This is a customer reply')
        ->call('submitReply')
        ->assertHasNoErrors()
        ->assertSet('message', '');

    $this->assertDatabaseHas('ticket_replies', [
        'ticket_id' => $this->ticket->id,
        'message' => '<p>This is a customer reply</p>',
        'is_internal' => false,
        'user_id' => null,
        'customer_name' => 'John Doe',
    ]);
});

it('limits customer reply to 500 chars', function () {
    Livewire::test(TicketConversation::class, ['ticket' => $this->ticket])
        ->set('message', str_repeat('a', 501))
        ->call('submitReply')
        ->assertHasErrors(['message' => 'max']);
});

it('allows customer to upload up to 2 files', function () {
    Storage::fake('public');

    $file1 = UploadedFile::fake()->image('photo1.jpg');
    $file2 = UploadedFile::fake()->create('doc.pdf', 1000);
    $file3 = UploadedFile::fake()->image('photo3.jpg');

    // Test max 2 validation
    Livewire::test(TicketConversation::class, ['ticket' => $this->ticket])
        ->set('attachments', [$file1, $file2, $file3])
        ->call('submitReply')
        ->assertHasErrors(['attachments' => 'max']);

    // Test successful upload
    Livewire::test(TicketConversation::class, ['ticket' => $this->ticket])
        ->set('message', 'Here are my files')
        ->set('attachments', [$file1, $file2])
        ->call('submitReply')
        ->assertHasNoErrors();

    $reply = $this->ticket->replies()->latest()->first();
    expect($reply->attachments)->toHaveCount(2);
    expect($reply->attachments[0]['name'])->toBe('photo1.jpg');

    Storage::disk('public')->assertExists($reply->attachments[0]['path']);
});

it('allows admin to reply as themselves', function () {
    $this->actingAs($this->user);

    Livewire::test(TicketDetails::class, ['ticket' => $this->ticket])
        ->set('message', 'Hello from admin')
        ->call('addReply')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('ticket_replies', [
        'ticket_id' => $this->ticket->id,
        'message' => '<p>Hello from admin</p>',
        'user_id' => $this->user->id,
        'is_technician' => false,
    ]);
});

it('allows admin to reply disguised as another agent', function () {
    $this->actingAs($this->user);

    $otherAgent = User::factory()->create(['company_id' => $this->company->id, 'role' => 'agent']);

    Livewire::test(TicketDetails::class, ['ticket' => $this->ticket])
        ->set('message', 'Hello from technician')
        ->set('senderId', $otherAgent->id)
        ->call('addReply')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('ticket_replies', [
        'ticket_id' => $this->ticket->id,
        'message' => '<p>Hello from technician</p>',
        'user_id' => $otherAgent->id,
        'is_technician' => false,
    ]);
});

it('reopens resolved ticket to open status on new customer reply', function () {
    $this->ticket->update(['status' => 'resolved']);

    Livewire::test(TicketConversation::class, ['ticket' => $this->ticket])
        ->set('message', 'I still need help')
        ->call('submitReply')
        ->assertHasNoErrors();

    expect($this->ticket->fresh()->status)->toBe('open');
});

it('notifies assigned agent when client replies', function () {
    Notification::fake();

    $agent = User::factory()->create(['company_id' => $this->company->id, 'role' => 'operator']);
    $this->ticket->update(['assigned_to' => $agent->id]);

    Livewire::test(TicketConversation::class, ['ticket' => $this->ticket])
        ->set('message', 'Client reply')
        ->call('submitReply')
        ->assertHasNoErrors();

    Notification::assertSentTo($agent, \App\Notifications\ClientReplied::class);
});

it('sends email to customer with tracking link when agent replies', function () {
    Mail::fake();

    $this->actingAs($this->user);

    Livewire::test(TicketDetails::class, ['ticket' => $this->ticket])
        ->set('message', 'We are looking into your request')
        ->call('addReply')
        ->assertHasNoErrors();

    Mail::assertQueued(AgentRepliedToTicket::class, function ($mail) {
        return $mail->hasTo($this->ticket->customer->email)
            && $mail->ticket->id === $this->ticket->id
            && str_contains($mail->reply->message, 'We are looking into your request');
    });
});

it('does not send agent reply email when ticket is not verified', function () {
    Mail::fake();

    $this->ticket->update(['verified' => false, 'verification_token' => null]);
    $this->actingAs($this->user);

    Livewire::test(TicketDetails::class, ['ticket' => $this->ticket])
        ->set('message', 'Agent reply to unverified ticket')
        ->call('addReply')
        ->assertHasNoErrors();

    Mail::assertNotSent(AgentRepliedToTicket::class);
});

it('notifies agent even when ticket was assigned after widget loaded', function () {
    Notification::fake();

    $agent = User::factory()->create(['company_id' => $this->company->id, 'role' => 'operator']);

    $component = Livewire::test(TicketConversation::class, ['ticket' => $this->ticket]);

    $this->ticket->update(['assigned_to' => $agent->id]);

    $component
        ->set('message', 'Late assignment reply')
        ->call('submitReply')
        ->assertHasNoErrors();

    Notification::assertSentTo($agent, \App\Notifications\ClientReplied::class);
});

it('shows SLA section in sidebar even when SLA policy is disabled', function () {
    $this->actingAs($this->user);

    SlaPolicy::create([
        'company_id' => $this->company->id,
        'is_enabled' => false,
        'low_minutes' => 480,
        'medium_minutes' => 240,
        'high_minutes' => 120,
        'urgent_minutes' => 60,
    ]);

    Livewire::test(TicketDetails::class, ['ticket' => $this->ticket])
        ->assertSee('SLA')
        ->assertSee('Disabled');
});

it('shows SLA countdown state in sidebar when SLA is enabled and due time exists', function () {
    $this->actingAs($this->user);

    SlaPolicy::create([
        'company_id' => $this->company->id,
        'is_enabled' => true,
        'low_minutes' => 480,
        'medium_minutes' => 240,
        'high_minutes' => 120,
        'urgent_minutes' => 60,
    ]);

    $this->ticket->update([
        'due_time' => now()->addHour(),
        'sla_status' => 'on_time',
    ]);

    Livewire::test(TicketDetails::class, ['ticket' => $this->ticket->fresh()])
        ->assertSee('Time remaining');
});

it('shows ticket description as the first message in conversation', function () {
    $this->actingAs($this->user);

    Livewire::test(TicketDetails::class, ['ticket' => $this->ticket])
        ->assertSee('Test Description')
        ->assertSee($this->customer->name);
});

it('shows assigned agent in reply-as dropdown for admin', function () {
    $agent = User::factory()->create(['company_id' => $this->company->id, 'role' => 'agent']);
    $this->ticket->update(['assigned_to' => $agent->id]);

    $this->actingAs($this->user);

    Livewire::test(TicketDetails::class, ['ticket' => $this->ticket->fresh()])
        ->assertSee('Reply as')
        ->assertSee($agent->name);
});

it('shows live polling and operator name typing indicator on tracking chat', function () {
    Cache::put('ticket:typing:agent:'.$this->ticket->id, 'Agent Smith', now()->addSeconds(6));

    Livewire::test(TicketConversation::class, ['ticket' => $this->ticket])
        ->assertSee('Agent Smith is typing')
        ->assertSeeHtml('wire:poll.5s');
});

it('shows fallback typing name for legacy boolean cache value', function () {
    Cache::put('ticket:typing:agent:'.$this->ticket->id, true, now()->addSeconds(6));

    Livewire::test(TicketConversation::class, ['ticket' => $this->ticket])
        ->assertSee('Support team is typing');
});

it('tracks and clears customer typing state in tracking chat', function () {
    $typingKey = 'ticket:typing:customer:'.$this->ticket->id;

    Livewire::test(TicketConversation::class, ['ticket' => $this->ticket])
        ->call('markTyping');

    expect(Cache::has($typingKey))->toBeTrue();

    Livewire::test(TicketConversation::class, ['ticket' => $this->ticket])
        ->set('message', 'sending now')
        ->call('submitReply')
        ->assertHasNoErrors();

    expect(Cache::has($typingKey))->toBeFalse();
});

it('shows customer typing indicator in operator ticket details view', function () {
    Cache::put('ticket:typing:customer:'.$this->ticket->id, true, now()->addSeconds(6));

    $this->actingAs($this->user);

    Livewire::test(TicketDetails::class, ['ticket' => $this->ticket])
        ->assertSee('Customer is typing');
});

it('sends tracking link email when creating a follow-up ticket', function () {
    Mail::fake();

    SlaPolicy::create([
        'company_id' => $this->company->id,
        'linked_ticket_days' => 30,
    ]);

    $this->ticket->update([
        'status' => 'closed',
        'closed_at' => now()->subDay(),
    ]);

    Livewire::test(TicketConversation::class, ['ticket' => $this->ticket->fresh()])
        ->set('message', 'I still need help with this')
        ->call('submitReply')
        ->set('message', 'I still need help with this')
        ->call('submitReply')
        ->assertHasNoErrors();

    $followUp = Ticket::where('parent_ticket_id', $this->ticket->id)->first();
    expect($followUp)->not->toBeNull();
    expect($followUp->tracking_token)->not->toBeEmpty();

    Mail::assertQueued(TicketVerified::class, function ($mail) {
        return $mail->hasTo($this->customer->email);
    });
});
