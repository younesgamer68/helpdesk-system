<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TicketScreenshotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::query()->firstOrCreate(
            ['slug' => 'screenshot-company'],
            [
                'name' => 'Screenshot Company',
                'email' => 'support@screenshot-company.test',
                'phone' => '+212600000000',
                'require_client_verification' => false,
            ]
        );

        $categories = [
            TicketCategory::query()->firstOrCreate(
                ['company_id' => $company->id, 'name' => 'Technical'],
                ['description' => 'Technical issues', 'default_priority' => 'high']
            ),
            TicketCategory::query()->firstOrCreate(
                ['company_id' => $company->id, 'name' => 'Billing'],
                ['description' => 'Billing and invoices', 'default_priority' => 'medium']
            ),
            TicketCategory::query()->firstOrCreate(
                ['company_id' => $company->id, 'name' => 'General'],
                ['description' => 'General questions', 'default_priority' => 'low']
            ),
        ];

        $operators = [
            User::query()->updateOrCreate(
                ['email' => 'screenshot-agent-1@example.com'],
                [
                    'company_id' => $company->id,
                    'name' => 'Screenshot Agent One',
                    'role' => 'operator',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            ),
            User::query()->updateOrCreate(
                ['email' => 'screenshot-agent-2@example.com'],
                [
                    'company_id' => $company->id,
                    'name' => 'Screenshot Agent Two',
                    'role' => 'operator',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            ),
        ];

        Ticket::withTrashed()
            ->where('company_id', $company->id)
            ->where('subject', 'like', '[Screenshot] %')
            ->forceDelete();

        $subjects = [
            'Login page timeout',
            'Cannot reset password',
            'Invoice mismatch for March',
            'Export CSV takes too long',
            'Mobile layout overlap',
            'API returns 500 on POST',
            'Need account ownership transfer',
            'Notification email delay',
            'Critical payment webhook failed',
            'Feature request: bulk archive',
        ];

        $statuses = collect([
            'pending', 'open', 'in_progress', 'resolved', 'closed',
            'open', 'pending', 'in_progress', 'resolved', 'closed',
        ])->shuffle()->values()->all();

        $priorities = collect([
            'low', 'low', 'low',
            'medium', 'medium', 'medium',
            'high', 'high', 'high',
            'urgent',
        ])->shuffle()->values()->all();

        $slaStatuses = collect([
            'on_time', 'on_time', 'at_risk', 'breached', 'on_time',
            'breached', 'at_risk', 'on_time', 'breached', 'on_time',
        ])->shuffle()->values()->all();

        $assigneeIds = [$operators[0]->id, $operators[1]->id, null];

        foreach ($subjects as $index => $subject) {
            $status = $statuses[$index];
            $slaStatus = $slaStatuses[$index];

            $dueTime = match ($slaStatus) {
                'breached' => now()->subHours(random_int(2, 18)),
                'at_risk' => now()->addHours(random_int(1, 3)),
                default => now()->addHours(random_int(8, 72)),
            };

            $createdAt = now()->subHours(random_int(1, 120));
            $updatedAt = (clone $createdAt)->addHours(random_int(0, 24));

            $resolvedAt = in_array($status, ['resolved', 'closed'], true)
                ? (clone $createdAt)->addHours(random_int(1, 12))
                : null;

            $closedAt = $status === 'closed' && $resolvedAt !== null
                ? (clone $resolvedAt)->addHours(random_int(1, 6))
                : null;

            Ticket::withoutEvents(function () use ($assigneeIds, $categories, $company, $createdAt, $dueTime, $index, $priorities, $resolvedAt, $closedAt, $slaStatus, $status, $subject, $updatedAt): void {
                Ticket::factory()->create([
                    'company_id' => $company->id,
                    'category_id' => $categories[$index % count($categories)]->id,
                    'assigned_to' => $assigneeIds[array_rand($assigneeIds)],
                    'subject' => '[Screenshot] '.$subject,
                    'description' => 'Seeded screenshot ticket with randomized status, priority, and SLA state for table screenshots.',
                    'status' => $status,
                    'priority' => $priorities[$index],
                    'sla_status' => $slaStatus,
                    'due_time' => $dueTime,
                    'resolved_at' => $resolvedAt,
                    'closed_at' => $closedAt,
                    'verified' => true,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);
            });
        }

        $this->command?->info('Seeded 10 screenshot tickets with mixed priorities, statuses, and SLA states.');
    }
}
