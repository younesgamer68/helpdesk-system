<?php

use App\Mail\TicketVerification;
use App\Mail\TicketVerified;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WidgetSetting;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->company = Company::factory()->create([
        'onboarding_completed_at' => now(),
        'require_client_verification' => true,
    ]);
    $this->admin = User::factory()->admin()->create(['company_id' => $this->company->id]);
    $this->widget = WidgetSetting::create([
        'company_id' => $this->company->id,
        'theme_mode' => 'dark',
        'form_title' => 'Test Widget',
        'welcome_message' => 'Hello',
        'success_message' => 'Thanks!',
        'default_status' => 'open',
        'default_priority' => 'medium',
        'is_active' => true,
    ]);
});

test('widget submit sends verification email when require_client_verification is true', function () {
    Mail::fake();

    $response = $this->withHeader('Host', $this->company->slug.'.'.config('app.domain'))
        ->postJson(route('widget.submit', ['company' => $this->company->slug, 'key' => $this->widget->widget_key]), [
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'subject' => 'Need Help',
            'description' => 'Something is broken',
        ]);

    $response->assertSuccessful();

    $ticket = Ticket::where('company_id', $this->company->id)->first();
    expect($ticket->verified)->toBe(0);
    expect($ticket->source)->toBe('widget');

    Mail::assertQueued(TicketVerification::class);
    Mail::assertNotQueued(TicketVerified::class);
});

test('widget submit auto-verifies when require_client_verification is false', function () {
    Mail::fake();

    $this->company->update(['require_client_verification' => false]);

    $response = $this->withHeader('Host', $this->company->slug.'.'.config('app.domain'))
        ->postJson(route('widget.submit', ['company' => $this->company->slug, 'key' => $this->widget->widget_key]), [
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'subject' => 'Need Help',
            'description' => 'Something is broken',
        ]);

    $response->assertSuccessful();

    $ticket = Ticket::where('company_id', $this->company->id)->first();
    expect($ticket->verified)->toBe(1);
    expect($ticket->tracking_token)->not->toBeNull();
    expect($ticket->source)->toBe('widget');

    Mail::assertQueued(TicketVerified::class);
    Mail::assertNotQueued(TicketVerification::class);
});

test('tickets created by agent have source set to agent', function () {
    $this->actingAs($this->admin);

    Livewire\Livewire::test(\App\Livewire\Tickets\TicketsTable::class)
        ->set('customer_name', 'John Doe')
        ->set('customer_email', 'john@example.com')
        ->set('subject', 'Agent ticket')
        ->set('description', 'Created by agent')
        ->set('priority', 'medium')
        ->call('createTicket')
        ->assertHasNoErrors();

    $ticket = Ticket::where('company_id', $this->company->id)->first();
    expect($ticket->source)->toBe('agent');
});

test('widget submit updates existing customer name for matching email', function () {
    Customer::create([
        'company_id' => $this->company->id,
        'name' => 'Hilary Warner',
        'email' => 'customer@example.com',
        'phone' => null,
        'is_active' => true,
    ]);

    $response = $this->withHeader('Host', $this->company->slug.'.'.config('app.domain'))
        ->postJson(route('widget.submit', ['company' => $this->company->slug, 'key' => $this->widget->widget_key]), [
            'customer_name' => 'Bilal Newname',
            'customer_email' => 'customer@example.com',
            'subject' => 'Identity sync test',
            'description' => 'Name should come from latest widget form input',
        ]);

    $response->assertSuccessful();

    $updatedCustomer = Customer::where('company_id', $this->company->id)
        ->where('email', 'customer@example.com')
        ->first();

    expect($updatedCustomer)->not->toBeNull();
    expect($updatedCustomer->name)->toBe('Bilal Newname');
});
