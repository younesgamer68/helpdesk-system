<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Models\TicketCategory;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test company
        $company = Company::create([
            'name' => 'Acme Corporation',
            'slug' => 'acme-corporation',
            'email' => 'support@acme.com',
            'phone' => '+1234567890',
            'require_client_verification' => false,
        ]);

        // Create admin user
        $admin = User::create([
            'company_id' => $company->id,
            'name' => 'Admin User',
            'email' => 'admin@acme.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create technician user
        $tech = User::create([
            'company_id' => $company->id,
            'name' => 'Tech Support',
            'email' => 'tech@acme.com',
            'password' => bcrypt('password'),
            'role' => 'technician',
            'email_verified_at' => now(),
        ]);

        // Create more agents for variety
        $agent1 = User::create([
            'company_id' => $company->id,
            'name' => 'Sarah Williams',
            'email' => 'sarah@acme.com',
            'password' => bcrypt('password'),
            'role' => 'technician',
            'email_verified_at' => now(),
        ]);

        $agent2 = User::create([
            'company_id' => $company->id,
            'name' => 'Mike Johnson',
            'email' => 'mike@acme.com',
            'password' => bcrypt('password'),
            'role' => 'technician',
            'email_verified_at' => now(),
        ]);

        $agents = [$tech, $agent1, $agent2];

        // Create categories
        $hardwareCategory = TicketCategory::create([
            'company_id' => $company->id,
            'name' => 'Hardware Issues',
            'description' => 'Problems with physical devices',
            'color' => '#ef4444',
            'default_priority' => 'high',
        ]);

        $softwareCategory = TicketCategory::create([
            'company_id' => $company->id,
            'name' => 'Software Bugs',
            'description' => 'Software errors and bugs',
            'color' => '#f59e0b',
            'default_priority' => 'medium',
        ]);

        $generalCategory = TicketCategory::create([
            'company_id' => $company->id,
            'name' => 'General Questions',
            'description' => 'General inquiries and questions',
            'color' => '#10b981',
            'default_priority' => 'low',
        ]);

        $uiCategory = TicketCategory::create([
            'company_id' => $company->id,
            'name' => 'UI Issues',
            'description' => 'Looks and views issues',
            'color' => '#10b901',
            'default_priority' => 'low',
        ]);

        $categories = [$hardwareCategory, $softwareCategory, $generalCategory, $uiCategory];

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

        foreach ($ticketCounts as $status => $count) {
            $this->command->info("Creating {$count} {$status} tickets...");

            Ticket::factory()
                ->count($count)
                ->state(fn() => [
                    'company_id' => $company->id,
                    'assigned_to' => fake()->randomElement($agentIds),
                    'category_id' => fake()->randomElement($categoryIds),
                    'status' => $status,
                ])
                ->create();
        }

        // Create some urgent tickets
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

        $totalTickets = Ticket::count();
        $this->command->info("✅ Successfully seeded {$totalTickets} tickets!");
        $this->command->info("📊 Breakdown:");
        foreach ($ticketCounts as $status => $count) {
            $this->command->info("   - {$status}: {$count}");
        }
        $this->command->info("   - urgent: 20");
    }
}