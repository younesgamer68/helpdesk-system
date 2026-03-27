<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test company
        $company = Company::query()->updateOrCreate(
            ['slug' => 'acme-corporation'],
            [
                'name' => 'Acme Corporation',
                'email' => 'support@acme.com',
                'phone' => '+1234567890',
                'require_client_verification' => false,
            ]
        );

        // Create admin user
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@acme.com'],
            [
                'company_id' => $company->id,
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Create operator user
        $tech = User::query()->updateOrCreate(
            ['email' => 'tech@example.com'],
            [
                'company_id' => $company->id,
                'name' => 'Support Operator',
                'password' => bcrypt('password'),
                'role' => 'operator',
                'email_verified_at' => now(),
            ]
        );

        // Create more agents for variety
        $agent1 = User::query()->updateOrCreate(
            ['email' => 'sarah@acme.com'],
            [
                'company_id' => $company->id,
                'name' => 'Sarah Williams',
                'password' => bcrypt('password'),
                'role' => 'operator',
                'email_verified_at' => now(),
            ]
        );

        $agent2 = User::query()->updateOrCreate(
            ['email' => 'mike@acme.com'],
            [
                'company_id' => $company->id,
                'name' => 'Mike Johnson',
                'password' => bcrypt('password'),
                'role' => 'operator',
                'email_verified_at' => now(),
            ]
        );

        $agents = [$tech, $agent1, $agent2];

        // Create categories
        $hardwareCategory = TicketCategory::query()->updateOrCreate(
            ['company_id' => $company->id, 'name' => 'Hardware Issues'],
            ['description' => 'Problems with physical devices', 'default_priority' => 'high']
        );

        $softwareCategory = TicketCategory::query()->updateOrCreate(
            ['company_id' => $company->id, 'name' => 'Software Bugs'],
            ['description' => 'Software errors and bugs', 'default_priority' => 'medium']
        );

        $generalCategory = TicketCategory::query()->updateOrCreate(
            ['company_id' => $company->id, 'name' => 'General Questions'],
            ['description' => 'General inquiries and questions', 'default_priority' => 'low']
        );

        $uiCategory = TicketCategory::query()->updateOrCreate(
            ['company_id' => $company->id, 'name' => 'UI Issues'],
            ['description' => 'Looks and views issues', 'default_priority' => 'low']
        );

        $categories = [$hardwareCategory, $softwareCategory, $generalCategory, $uiCategory];

        // Seed one deterministic demo conversation matching the requested transcript.
        $noelia = Customer::query()->updateOrCreate(
            ['company_id' => $company->id, 'email' => 'stokes.dock@example.net'],
            ['name' => 'Noelia Bode', 'is_active' => true]
        );

        $conversationTicket = Ticket::query()->updateOrCreate(
            ['company_id' => $company->id, 'ticket_number' => 'TKT-773193'],
            [
                'customer_id' => $noelia->id,
                'subject' => 'Cannot access my account',
                'description' => "As I mentioned in the subject line, I cannot access my account. When I type in my login credentials and click 'Sign In', the page just loads endlessly and eventually gives me a '504 Gateway Timeout' error. It doesn't tell me my password is wrong, it just won't log me in.",
                'status' => 'pending',
                'priority' => 'urgent',
                'assigned_to' => $admin->id,
                'category_id' => $generalCategory->id,
                'verified' => true,
                'verification_token' => null,
                'tracking_token' => Str::random(64),
                'source' => 'web',
                'created_at' => now()->subMinutes(6),
                'updated_at' => now(),
                'resolved_at' => null,
                'closed_at' => null,
            ]
        );

        TicketReply::query()->where('ticket_id', $conversationTicket->id)->delete();

        TicketReply::query()->create([
            'ticket_id' => $conversationTicket->id,
            'user_id' => null,
            'customer_name' => 'Noelia Bode',
            'message' => 'I tried on multiple browsers (Chrome, Firefox, Safari) and the issue occurs on all of them.',
            'is_internal' => false,
            'is_technician' => false,
            'created_at' => now()->subMinutes(5),
            'updated_at' => now()->subMinutes(5),
        ]);

        TicketReply::query()->create([
            'ticket_id' => $conversationTicket->id,
            'user_id' => $admin->id,
            'customer_name' => null,
            'message' => 'Could you please describe the specific issue you are experiencing on multiple browsers?',
            'is_internal' => false,
            'is_technician' => true,
            'created_at' => now()->subMinutes(4),
            'updated_at' => now()->subMinutes(4),
        ]);

        TicketReply::query()->create([
            'ticket_id' => $conversationTicket->id,
            'user_id' => null,
            'customer_name' => 'Noelia Bode',
            'message' => "As I mentioned in the subject line, I cannot access my account. When I type in my login credentials and click 'Sign In', the page just loads endlessly and eventually gives me a '504 Gateway Timeout' error. It doesn't tell me my password is wrong, it just won't log me in.",
            'is_internal' => false,
            'is_technician' => false,
            'created_at' => now()->subMinutes(3),
            'updated_at' => now()->subMinutes(3),
        ]);

        TicketReply::query()->create([
            'ticket_id' => $conversationTicket->id,
            'user_id' => $admin->id,
            'customer_name' => null,
            'message' => "Thank you for clarifying that, Noelia, and I apologize for missing the subject line! Since you are receiving a timeout error across all browsers, this points to an issue on our end rather than your local cache. I am going to reset your active background sessions and clear your account's server cache. Could you please wait about 5 minutes and try logging in one more time? If it still fails, I will escalate this to our technical team immediately since this ticket is marked as urgent.",
            'is_internal' => false,
            'is_technician' => true,
            'created_at' => now()->subMinutes(2),
            'updated_at' => now()->subMinutes(2),
        ]);

        // Seed one deterministic client with exactly 20 tickets.
        $demoClient = Customer::query()->updateOrCreate(
            ['company_id' => $company->id, 'email' => 'client20@example.com'],
            ['name' => 'Client Twenty', 'is_active' => true]
        );

        $statusCycle = ['pending', 'open', 'in_progress', 'resolved', 'closed'];
        $priorityCycle = ['low', 'medium', 'high', 'urgent'];
        $assignedCycle = [$tech->id, $agent1->id, $agent2->id, null];
        $deterministicCategoryIds = collect($categories)->pluck('id')->toArray();

        foreach (range(1, 20) as $index) {
            $status = $statusCycle[($index - 1) % count($statusCycle)];
            $priority = $priorityCycle[($index - 1) % count($priorityCycle)];
            $assignedTo = $assignedCycle[($index - 1) % count($assignedCycle)];
            $categoryId = $deterministicCategoryIds[($index - 1) % count($deterministicCategoryIds)];

            $createdAt = now()->subDays(40 - $index);
            $resolvedAt = in_array($status, ['resolved', 'closed'], true)
                ? (clone $createdAt)->addDay()
                : null;
            $closedAt = $status === 'closed'
                ? (clone $createdAt)->addDays(2)
                : null;

            Ticket::query()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'ticket_number' => 'TKT-CLIENT20-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                ],
                [
                    'customer_id' => $demoClient->id,
                    'subject' => "Client Twenty demo ticket #{$index}",
                    'description' => "Deterministic seeded ticket {$index} for demo client with 20 tickets.",
                    'status' => $status,
                    'priority' => $priority,
                    'assigned_to' => $assignedTo,
                    'category_id' => $categoryId,
                    'verified' => true,
                    'verification_token' => null,
                    'tracking_token' => hash('sha256', "client20-ticket-{$company->id}-{$index}"),
                    'source' => 'web',
                    'created_at' => $createdAt,
                    'updated_at' => now(),
                    'resolved_at' => $resolvedAt,
                    'closed_at' => $closedAt,
                ]
            );
        }

        $hasExistingFactoryTickets = Ticket::query()
            ->where('company_id', $company->id)
            ->where('ticket_number', '!=', 'TKT-773193')
            ->where('ticket_number', 'not like', 'TKT-CLIENT20-%')
            ->exists();

        // Generate tickets using factory
        $this->command->info('Generating tickets...');

        // Create different distributions of tickets
        $ticketCounts = [
            'pending' => 50,
            'open' => 150,
            'in_progress' => 100,
            'resolved' => 200,
            'closed' => 100,
        ];

        $agentIds = [$tech->id, $agent1->id, $agent2->id, null];
        $categoryIds = collect($categories)->pluck('id')->toArray();

        if (! $hasExistingFactoryTickets) {
            foreach ($ticketCounts as $status => $count) {
                $this->command->info("Creating {$count} {$status} tickets...");

                Ticket::factory()
                    ->count($count)
                    ->state(fn () => [
                        'company_id' => $company->id,
                        'assigned_to' => fake()->randomElement($agentIds),
                        'category_id' => fake()->randomElement($categoryIds),
                        'status' => $status,
                    ])
                    ->create();
            }
        } else {
            $this->command->info('Skipping bulk ticket generation (already seeded for this company).');
        }

        // Create some urgent tickets
        if (! $hasExistingFactoryTickets) {
            $this->command->info('Creating urgent tickets...');
            Ticket::factory()
                ->count(20)
                ->urgent()
                ->state([
                    'company_id' => $company->id,
                    'assigned_to' => fake()->randomElement([$tech->id, $agent1->id, $agent2->id]),
                    'category_id' => fake()->randomElement($categories)->id,
                    'status' => fake()->randomElement(['open', 'in_progress']),
                ])
                ->create();
        }

        $totalTickets = Ticket::count();
        $this->command->info("✅ Successfully seeded {$totalTickets} tickets!");
        $this->command->info('📊 Breakdown:');
        foreach ($ticketCounts as $status => $count) {
            $this->command->info("   - {$status}: {$count}");
        }
        $this->command->info('   - urgent: 20');
    }
}
