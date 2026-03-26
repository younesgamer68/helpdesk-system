<?php

use App\Livewire\Dashboard\OperatorsTable;
use App\Models\Company;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('operators table handles bulk invites', function () {
    $company = Company::factory()->create();
    /** @var User $admin */
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin', 'email_verified_at' => now()]);

    actingAs($admin);
    Mail::fake();

    Livewire::actingAs($admin)
        ->test(OperatorsTable::class)
        ->set('bulkInviteEmails', "test1@example.com\ntest2@example.com, test3@example.com")
        ->set('bulkInviteRole', 'operator')
        ->call('processBulkInvite')
        ->assertDispatched('show-toast');

    assertDatabaseHas('users', ['email' => 'test1@example.com', 'company_id' => $company->id]);
    assertDatabaseHas('users', ['email' => 'test2@example.com', 'company_id' => $company->id]);
    assertDatabaseHas('users', ['email' => 'test3@example.com', 'company_id' => $company->id]);
    expect(User::withoutGlobalScopes()->where('email', 'test1@example.com')->first()?->invite_expires_at)->not->toBeNull();
    Mail::assertQueued(\App\Mail\UserInvitationMail::class, 3);
});

test('operators table skips bulk invite emails that already exist in another company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    /** @var User $admin */
    $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'admin', 'email_verified_at' => now()]);

    User::withoutGlobalScopes()->create([
        'name' => 'Existing User',
        'email' => 'existing@example.com',
        'role' => 'operator',
        'password' => Hash::make('password'),
        'company_id' => $otherCompany->id,
    ]);

    actingAs($admin);
    Mail::fake();

    Livewire::actingAs($admin)
        ->test(OperatorsTable::class)
        ->set('bulkInviteEmails', "existing@example.com\nnew@example.com")
        ->set('bulkInviteRole', 'operator')
        ->call('processBulkInvite')
        ->assertDispatched('show-toast');

    expect(User::withoutGlobalScopes()->where('email', 'existing@example.com')->count())->toBe(1);
    assertDatabaseHas('users', ['email' => 'new@example.com', 'company_id' => $company->id]);
    Mail::assertQueued(\App\Mail\UserInvitationMail::class, 1);
});

test('operators table handles bulk removal with ticket reassignment', function () {
    $company = Company::factory()->create();
    /** @var User $admin */
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

    actingAs($admin);

    Livewire::actingAs($admin)
        ->test(OperatorsTable::class)
        ->set('selected', [$operator1->id, $operator2->id])
        ->call('bulkRemoveMembers')
        ->assertDispatched('show-toast');

    assertDatabaseMissing('users', ['id' => $operator1->id]);
    assertDatabaseMissing('users', ['id' => $operator2->id]);
    expect($ticket1->fresh()->assigned_to)->toBeNull();
    expect($ticket2->fresh()->assigned_to)->toBeNull();
});

test('operators table renders member specialities and ticket counts', function () {
    $company = Company::factory()->create();
    /** @var User $admin */
    $admin = User::factory()->admin()->create(['company_id' => $company->id, 'email_verified_at' => now()]);
    $operator = User::factory()->operator()->create(['company_id' => $company->id]);
    $category = TicketCategory::factory()->create(['company_id' => $company->id]);
    $operator->categories()->attach($category);

    Ticket::factory()->create(['company_id' => $company->id, 'assigned_to' => $operator->id, 'status' => 'open']);

    actingAs($admin);

    Livewire::actingAs($admin)
        ->test(OperatorsTable::class)
        ->assertSee($category->name)
        ->assertSee('1');
});

test('revoking a pending invite removes operator row from database', function () {
    $company = Company::factory()->create();
    /** @var User $admin */
    $admin = User::factory()->admin()->create([
        'company_id' => $company->id,
        'email_verified_at' => now(),
    ]);

    $pendingInvite = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
        'password' => null,
        'google_id' => null,
    ]);

    actingAs($admin);

    Livewire::actingAs($admin)
        ->test(OperatorsTable::class)
        ->call('removeUser', $pendingInvite->id)
        ->assertDispatched('show-toast');

    expect(User::withoutGlobalScopes()->withTrashed()->whereKey($pendingInvite->id)->exists())->toBeFalse();
});

test('bulk revoke removes pending invite rows from database', function () {
    $company = Company::factory()->create();
    /** @var User $admin */
    $admin = User::factory()->admin()->create([
        'company_id' => $company->id,
        'email_verified_at' => now(),
    ]);

    $pendingOne = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
        'password' => null,
        'google_id' => null,
    ]);

    $pendingTwo = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
        'password' => null,
        'google_id' => null,
    ]);

    actingAs($admin);

    Livewire::actingAs($admin)
        ->test(OperatorsTable::class)
        ->set('selected', [$pendingOne->id, $pendingTwo->id])
        ->call('bulkRevokeInvites')
        ->assertDispatched('show-toast');

    expect(User::withoutGlobalScopes()->withTrashed()->whereKey($pendingOne->id)->exists())->toBeFalse();
    expect(User::withoutGlobalScopes()->withTrashed()->whereKey($pendingTwo->id)->exists())->toBeFalse();
});

test('pending invite operator profile route is not accessible', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $admin = User::factory()->admin()->create([
        'company_id' => $company->id,
        'email_verified_at' => now(),
    ]);

    $pendingInvite = User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
        'password' => null,
        'google_id' => null,
    ]);

    actingAs($admin)
        ->get("http://{$company->slug}.".config('app.domain')."/operators/{$pendingInvite->id}")
        ->assertNotFound();
});

test('pending invites display expiration countdown in operators table', function () {
    $company = Company::factory()->create();
    /** @var User $admin */
    $admin = User::factory()->admin()->create([
        'company_id' => $company->id,
        'email_verified_at' => now(),
    ]);

    User::factory()->create([
        'company_id' => $company->id,
        'role' => 'operator',
        'password' => null,
        'google_id' => null,
        'invite_expires_at' => now()->addHours(5),
    ]);

    actingAs($admin);

    Livewire::actingAs($admin)
        ->test(OperatorsTable::class)
        ->assertSee('Expiring in')
        ->assertSee('hour');
});
