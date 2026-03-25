<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LandingPageTicketSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 1;

        // Clean up previous landing page seeded data
        Ticket::withTrashed()
            ->where('company_id', $companyId)
            ->whereIn('subject', [
                'Payment gateway returning 502 errors',
                'Cannot export monthly invoice as PDF',
                'Dashboard widgets not loading after update',
                'Kubernetes pod crash loop on staging',
                'Need help setting up SSO integration',
                'Mobile app login screen freezes on iOS 18',
                'Request to increase API rate limit',
                'Sidebar navigation misaligned on Firefox',
            ])
            ->forceDelete();

        // --- Teams ---
        $teams = collect([
            ['name' => 'Billing Support', 'color' => '#F59E0B', 'description' => 'Handles billing and payment issues'],
            ['name' => 'Technical Support', 'color' => '#3B82F6', 'description' => 'Handles technical and infrastructure issues'],
            ['name' => 'Customer Success', 'color' => '#10B981', 'description' => 'Handles onboarding and customer satisfaction'],
            ['name' => 'DevOps Engineering', 'color' => '#8B5CF6', 'description' => 'Handles deployments, uptime and infrastructure'],
        ])->map(function ($data) use ($companyId) {
            return Team::query()->firstOrCreate(
                ['company_id' => $companyId, 'name' => $data['name']],
                ['description' => $data['description'], 'color' => $data['color']]
            );
        });

        // --- Agents (operators) ---
        $agentData = [
            ['name' => 'Emma Rodriguez', 'email' => 'lp-emma@helpdesk.test'],
            ['name' => 'James Chen', 'email' => 'lp-james@helpdesk.test'],
            ['name' => 'Aisha Patel', 'email' => 'lp-aisha@helpdesk.test'],
            ['name' => 'Lucas Silva', 'email' => 'lp-lucas@helpdesk.test'],
            ['name' => 'Olivia Müller', 'email' => 'lp-olivia@helpdesk.test'],
            ['name' => 'David Kim', 'email' => 'lp-david@helpdesk.test'],
        ];

        $agents = collect($agentData)->map(function ($data) use ($companyId) {
            return User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'company_id' => $companyId,
                    'name' => $data['name'],
                    'role' => 'operator',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_available' => true,
                    'status' => 'online',
                ]
            );
        });

        // Assign agents to teams (2 per team, some overlap)
        $teamAgentMap = [
            0 => [0, 1],    // Billing Support: Emma, James
            1 => [2, 3],    // Technical Support: Aisha, Lucas
            2 => [4, 0],    // Customer Success: Olivia, Emma
            3 => [5, 3],    // DevOps Engineering: David, Lucas
        ];

        foreach ($teamAgentMap as $teamIndex => $agentIndexes) {
            $team = $teams[$teamIndex];
            foreach ($agentIndexes as $i => $agentIndex) {
                $team->members()->syncWithoutDetaching([
                    $agents[$agentIndex]->id => ['role' => $i === 0 ? 'lead' : 'member'],
                ]);
            }
        }

        // --- Categories (reuse existing or create) ---
        $categories = collect([
            ['name' => 'Hardware Issues', 'default_priority' => 'high'],
            ['name' => 'Software Bugs', 'default_priority' => 'high'],
            ['name' => 'General Questions', 'default_priority' => 'low'],
            ['name' => 'UI Issues', 'default_priority' => 'medium'],
        ])->map(function ($data) use ($companyId) {
            return TicketCategory::query()->firstOrCreate(
                ['company_id' => $companyId, 'name' => $data['name']],
                ['description' => $data['name'], 'default_priority' => $data['default_priority']]
            );
        });

        // --- Customers ---
        $customerData = [
            ['name' => 'Sarah Thompson', 'email' => 'sarah.thompson@acmecorp.com'],
            ['name' => 'Michael Rivera', 'email' => 'michael.r@techstartup.io'],
            ['name' => 'Priya Sharma', 'email' => 'priya@globalretail.com'],
            ['name' => 'Alex Dumont', 'email' => 'alex.dumont@financeplus.net'],
            ['name' => 'Fatima Al-Rashid', 'email' => 'fatima@logisticshub.com'],
            ['name' => 'Tom Nakamura', 'email' => 'tom.n@designworks.co'],
            ['name' => 'Clara Johansson', 'email' => 'clara.j@healthdata.org'],
            ['name' => 'Ryan O\'Brien', 'email' => 'ryan@cloudservices.dev'],
        ];

        $customers = collect($customerData)->map(function ($data) use ($companyId) {
            return Customer::query()->firstOrCreate(
                ['company_id' => $companyId, 'email' => $data['email']],
                ['name' => $data['name'], 'is_active' => true]
            );
        });

        // --- 8 Curated Tickets ---
        // Each ticket: unique subject, status, priority, agent, team, customer, category
        $tickets = [
            [
                'subject' => 'Payment gateway returning 502 errors',
                'status' => 'in_progress',
                'priority' => 'urgent',
                'agent' => 0,   // Emma Rodriguez
                'team' => 0,    // Billing Support
                'customer' => 3, // Alex Dumont
                'category' => 1, // Software Bugs
                'sla_status' => 'breached',
                'hours_ago' => 2,
            ],
            [
                'subject' => 'Cannot export monthly invoice as PDF',
                'status' => 'open',
                'priority' => 'high',
                'agent' => 1,   // James Chen
                'team' => 0,    // Billing Support
                'customer' => 0, // Sarah Thompson
                'category' => 1, // Software Bugs
                'sla_status' => 'at_risk',
                'hours_ago' => 8,
            ],
            [
                'subject' => 'Dashboard widgets not loading after update',
                'status' => 'pending',
                'priority' => 'medium',
                'agent' => 2,   // Aisha Patel
                'team' => 1,    // Technical Support
                'customer' => 1, // Michael Rivera
                'category' => 3, // UI Issues
                'sla_status' => 'on_time',
                'hours_ago' => 14,
            ],
            [
                'subject' => 'Kubernetes pod crash loop on staging',
                'status' => 'in_progress',
                'priority' => 'high',
                'agent' => 5,   // David Kim
                'team' => 3,    // DevOps Engineering
                'customer' => 7, // Ryan O'Brien
                'category' => 0, // Hardware Issues
                'sla_status' => 'at_risk',
                'hours_ago' => 5,
            ],
            [
                'subject' => 'Need help setting up SSO integration',
                'status' => 'open',
                'priority' => 'medium',
                'agent' => 4,   // Olivia Müller
                'team' => 2,    // Customer Success
                'customer' => 4, // Fatima Al-Rashid
                'category' => 2, // General Questions
                'sla_status' => 'on_time',
                'hours_ago' => 22,
            ],
            [
                'subject' => 'Mobile app login screen freezes on iOS 18',
                'status' => 'resolved',
                'priority' => 'medium',
                'agent' => 3,   // Lucas Silva
                'team' => 1,    // Technical Support
                'customer' => 5, // Tom Nakamura
                'category' => 1, // Software Bugs
                'sla_status' => 'on_time',
                'hours_ago' => 48,
            ],
            [
                'subject' => 'Request to increase API rate limit',
                'status' => 'closed',
                'priority' => 'low',
                'agent' => 4,   // Olivia Müller
                'team' => 2,    // Customer Success
                'customer' => 6, // Clara Johansson
                'category' => 2, // General Questions
                'sla_status' => 'on_time',
                'hours_ago' => 72,
            ],
            [
                'subject' => 'Sidebar navigation misaligned on Firefox',
                'status' => 'pending',
                'priority' => 'low',
                'agent' => 2,   // Aisha Patel
                'team' => 1,    // Technical Support
                'customer' => 2, // Priya Sharma
                'category' => 3, // UI Issues
                'sla_status' => 'on_time',
                'hours_ago' => 36,
            ],
        ];

        foreach ($tickets as $data) {
            $createdAt = now()->subHours($data['hours_ago']);
            $dueTime = match ($data['sla_status']) {
                'breached' => now()->subHours(random_int(1, 4)),
                'at_risk' => now()->addHours(random_int(1, 3)),
                default => now()->addHours(random_int(12, 48)),
            };

            $resolvedAt = in_array($data['status'], ['resolved', 'closed'], true)
                ? (clone $createdAt)->addHours(random_int(2, 10))
                : null;

            $closedAt = $data['status'] === 'closed' && $resolvedAt
                ? (clone $resolvedAt)->addHours(random_int(1, 4))
                : null;

            Ticket::withoutEvents(function () use ($companyId, $data, $teams, $agents, $customers, $categories, $createdAt, $dueTime, $resolvedAt, $closedAt): void {
                Ticket::factory()->create([
                    'company_id' => $companyId,
                    'subject' => $data['subject'],
                    'description' => 'This is a curated landing page demo ticket.',
                    'status' => $data['status'],
                    'priority' => $data['priority'],
                    'assigned_to' => $agents[$data['agent']]->id,
                    'team_id' => $teams[$data['team']]->id,
                    'customer_id' => $customers[$data['customer']]->id,
                    'category_id' => $categories[$data['category']]->id,
                    'sla_status' => $data['sla_status'],
                    'due_time' => $dueTime,
                    'verified' => true,
                    'source' => 'agent',
                    'resolved_at' => $resolvedAt,
                    'closed_at' => $closedAt,
                    'created_at' => $createdAt,
                    'updated_at' => now(),
                ]);
            });
        }

        $this->command?->info('Seeded 8 landing page tickets with diverse agents, teams, priorities, statuses, and customers.');
    }
}
