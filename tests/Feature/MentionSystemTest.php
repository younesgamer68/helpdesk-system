<?php

use App\Livewire\Tickets\TicketDetails;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketMention;
use App\Models\User;
use App\Notifications\UserMentioned;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function mentionSetup(): array
{
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $team = Team::factory()->create(['company_id' => $company->id]);
    $agent = User::factory()->create(['company_id' => $company->id, 'role' => 'agent']);
    $teammate = User::factory()->create(['company_id' => $company->id, 'role' => 'agent']);
    $team->members()->attach([$agent->id, $teammate->id], ['role' => 'member']);

    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $customer = Customer::factory()->create(['company_id' => $company->id]);

    $ticket = Ticket::create([
        'company_id' => $company->id,
        'ticket_number' => 'TKT-'.fake()->unique()->bothify('######'),
        'customer_id' => $customer->id,
        'subject' => fake()->sentence(4),
        'description' => fake()->paragraph(),
        'category_id' => $category->id,
        'priority' => 'medium',
        'status' => 'open',
        'assigned_to' => $agent->id,
        'verified' => true,
        'verification_token' => fake()->uuid(),
    ]);

    return [$agent, $teammate, $ticket, $company, $team];
}

it('creates mentions when adding internal note with mentioned users', function () {
    Notification::fake();
    [$agent, $teammate, $ticket] = mentionSetup();

    Livewire::actingAs($agent)
        ->test(TicketDetails::class, ['ticket' => $ticket])
        ->set('internalNote', 'Hey @'.$teammate->name.' check this out')
        ->set('mentionedUserIds', [$teammate->id])
        ->call('addInternalNote');

    expect(TicketMention::count())->toBe(1);

    $mention = TicketMention::first();
    expect($mention->mentioned_user_id)->toBe($teammate->id)
        ->and($mention->mentioned_by_user_id)->toBe($agent->id)
        ->and($mention->ticket_id)->toBe($ticket->id)
        ->and($mention->read_at)->toBeNull();
});

it('sends mention notification to mentioned user', function () {
    Notification::fake();
    [$agent, $teammate, $ticket] = mentionSetup();

    Livewire::actingAs($agent)
        ->test(TicketDetails::class, ['ticket' => $ticket])
        ->set('internalNote', 'Hey @'.$teammate->name.' please review')
        ->set('mentionedUserIds', [$teammate->id])
        ->call('addInternalNote');

    Notification::assertSentTo($teammate, UserMentioned::class);
});

it('creates mention even for self', function () {
    Notification::fake();
    [$agent, $teammate, $ticket] = mentionSetup();

    Livewire::actingAs($agent)
        ->test(TicketDetails::class, ['ticket' => $ticket])
        ->set('internalNote', 'Note to self @'.$agent->name)
        ->set('mentionedUserIds', [$agent->id])
        ->call('addInternalNote');

    expect(TicketMention::count())->toBe(1);
    Notification::assertSentTo($agent, UserMentioned::class);
});

it('does not mention users from different company', function () {
    Notification::fake();
    [$agent, $teammate, $ticket, $company] = mentionSetup();

    $otherCompany = Company::factory()->create();
    $outsider = User::factory()->create(['company_id' => $otherCompany->id, 'role' => 'agent']);

    Livewire::actingAs($agent)
        ->test(TicketDetails::class, ['ticket' => $ticket])
        ->set('internalNote', 'Hey @'.$outsider->name)
        ->set('mentionedUserIds', [$outsider->id])
        ->call('addInternalNote');

    expect(TicketMention::count())->toBe(0);
    Notification::assertNotSentTo($outsider, UserMentioned::class);
});

it('resets mentionedUserIds after adding note', function () {
    Notification::fake();
    [$agent, $teammate, $ticket] = mentionSetup();

    Livewire::actingAs($agent)
        ->test(TicketDetails::class, ['ticket' => $ticket])
        ->set('internalNote', 'Hey @'.$teammate->name)
        ->set('mentionedUserIds', [$teammate->id])
        ->call('addInternalNote')
        ->assertSet('mentionedUserIds', [])
        ->assertSet('internalNote', '');
});
