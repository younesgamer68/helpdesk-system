<?php

use App\Livewire\Dashboard\TicketDetails;
use App\Livewire\Widget\TicketConversation;
use App\Models\Company;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id, 'role' => 'admin']);
    $this->category = TicketCategory::factory()->create(['company_id' => $this->company->id]);

    $this->ticket = Ticket::create([
        'company_id' => $this->company->id,
        'ticket_number' => 'TKT-TEST1234',
        'customer_name' => 'John Doe',
        'customer_email' => 'john@example.com',
        'subject' => 'Test Subject',
        'description' => 'Test Description',
        'category_id' => $this->category->id,
        'priority' => 'low',
        'status' => 'open',
        'verified' => true,
        'verification_token' => 'test-token',
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

it('reopens closed or resolved ticket on new customer reply', function () {
    $this->ticket->update(['status' => 'resolved']);

    Livewire::test(TicketConversation::class, ['ticket' => $this->ticket])
        ->set('message', 'I still need help')
        ->call('submitReply')
        ->assertHasNoErrors();

    expect($this->ticket->fresh()->status)->toBe('open');
});
