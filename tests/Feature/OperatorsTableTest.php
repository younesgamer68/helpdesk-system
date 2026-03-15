<?php

use App\Livewire\Dashboard\OperatorsTable;
use App\Models\Company;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('operators table handles bulk invites', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin', 'email_verified_at' => now()]);

    $this->actingAs($admin);
    $this->withHeader('Host', $company->slug.'.'.config('app.domain'));
    \Illuminate\Support\Facades\Mail::fake();

    Livewire::test(OperatorsTable::class)
        ->set('bulkInviteEmails', "test1@example.com\ntest2@example.com, test3@example.com")
        ->set('bulkInviteRole', 'operator')
        ->call('processBulkInvite')
        ->assertDispatched('show-toast');

    $this->assertDatabaseHas('users', ['email' => 'test1@example.com', 'company_id' => $company->id]);
    $this->assertDatabaseHas('users', ['email' => 'test2@example.com', 'company_id' => $company->id]);
    $this->assertDatabaseHas('users', ['email' => 'test3@example.com', 'company_id' => $company->id]);
    \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\UserInvitationMail::class, 3);
});

test('operators table handles bulk removal with ticket reassignment', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'admin',
        'email_verified_at' => now(),
        'password' => Hash::make('password'),
    ]);

    $operator1 = User::factory()->operator()->create([
        'company_id' => $company->id,
        'password' => Hash::make('password'),
    ]);

    $operator2 = User::factory()->operator()->create([
        'company_id' => $company->id,
        'password' => Hash::make('password'),
    ]);

    $ticket1 = Ticket::factory()->create(['company_id' => $company->id, 'assigned_to' => $operator1->id]);
    $ticket2 = Ticket::factory()->create(['company_id' => $company->id, 'assigned_to' => $operator2->id]);

    $this->actingAs($admin);
    $this->withHeader('Host', $company->slug.'.'.config('app.domain'));

    Livewire::test(OperatorsTable::class)
        ->set('selected', [$operator1->id, $operator2->id])
        ->call('bulkRemoveMembers')
        ->assertDispatched('show-toast');

    $this->assertSoftDeleted('users', ['id' => $operator1->id]);
    $this->assertSoftDeleted('users', ['id' => $operator2->id]);
    expect($ticket1->fresh()->assigned_to)->toBeNull();
    expect($ticket2->fresh()->assigned_to)->toBeNull();
});

test('operators table renders member specialities and ticket counts', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->admin()->create(['company_id' => $company->id, 'email_verified_at' => now()]);
    $operator = User::factory()->operator()->create(['company_id' => $company->id]);
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $operator->categories()->attach($category);

    Ticket::factory()->create(['company_id' => $company->id, 'assigned_to' => $operator->id, 'status' => 'open']);

    $this->actingAs($admin);
    $this->withHeader('Host', $company->slug.'.'.config('app.domain'));

    Livewire::test(OperatorsTable::class)
        ->assertSee($category->name)
        ->assertSee('1');
});
