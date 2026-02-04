<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Models\TicketCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public $guarded = [];
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
        User::create([
            'company_id' => $company->id,
            'name' => 'Admin User',
            'email' => 'admin@acme.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create technician user
        User::create([
            'company_id' => $company->id,
            'name' => 'Tech Support',
            'email' => 'tech@acme.com',
            'password' => bcrypt('password'),
            'role' => 'technician',
            'email_verified_at' => now(),
        ]);

        // Create some categories
        TicketCategory::create([
            'company_id' => $company->id,
            'name' => 'Hardware Issues',
            'description' => 'Problems with physical devices',
            'color' => '#ef4444',
            'default_priority' => 'high',
        ]);

        TicketCategory::create([
            'company_id' => $company->id,
            'name' => 'Software Bugs',
            'description' => 'Software errors and bugs',
            'color' => '#f59e0b',
            'default_priority' => 'medium',
        ]);

        TicketCategory::create([
            'company_id' => $company->id,
            'name' => 'General Questions',
            'description' => 'General inquiries and questions',
            'color' => '#10b981',
            'default_priority' => 'low',
        ]);
        $tickets = [
            [
                'subject' => 'Login page not loading correctly on mobile',
                'description' => 'When I try to access the login page on my iPhone, it shows a blank screen. This has been happening since yesterday.',
                'priority' => 'high',
                'status' => 'open',
                'customer_name' => 'John Smith',
                'customer_email' => 'john.smith@example.com',
                'customer_phone' => '+1234567890',
                'category_id' => 1,
            ],
            [
                'subject' => 'Cannot reset password',
                'description' => 'I clicked on the forgot password link but never received the reset email. I checked my spam folder as well.',
                'priority' => 'urgent',
                'status' => 'in_progress',
                'customer_name' => 'Sarah Johnson',
                'customer_email' => 'sarah.j@example.com',
                'customer_phone' => '+1234567891',
                'category_id' => 2,
            ],
            [
                'subject' => 'Dashboard loading very slowly',
                'description' => 'The dashboard takes over 15 seconds to load. This is affecting my productivity significantly.',
                'priority' => 'high',
                'status' => 'pending',
                'customer_name' => 'Michael Brown',
                'customer_email' => 'michael.brown@example.com',
                'customer_phone' => null,
                'category_id' => 1,
            ],
            [
                'subject' => 'Feature request: Dark mode',
                'description' => 'It would be great to have a dark mode option for better viewing at night. Many users have requested this feature.',
                'priority' => 'low',
                'status' => 'open',
                'customer_name' => 'Emily Davis',
                'customer_email' => 'emily.davis@example.com',
                'customer_phone' => '+1234567892',
                'category_id' => 3,
            ],
            [
                'subject' => 'Error when uploading profile picture',
                'description' => 'When I try to upload a profile picture larger than 2MB, I get an error message saying "Upload failed". Please increase the file size limit.',
                'priority' => 'medium',
                'status' => 'resolved',
                'customer_name' => 'David Wilson',
                'customer_email' => 'david.w@example.com',
                'customer_phone' => '+1234567893',
                'category_id' => 1,
            ],
            [
                'subject' => 'Email notifications not working',
                'description' => 'I am not receiving any email notifications for new messages or updates. I have checked my notification settings and everything is enabled.',
                'priority' => 'medium',
                'status' => 'in_progress',
                'customer_name' => 'Jessica Martinez',
                'customer_email' => 'jessica.m@example.com',
                'customer_phone' => '+1234567894',
                'category_id' => 2,
            ],
            [
                'subject' => 'How to export data to CSV?',
                'description' => 'I need to export my data to CSV format for backup purposes. Could you please guide me on how to do this?',
                'priority' => 'low',
                'status' => 'closed',
                'customer_name' => 'Robert Taylor',
                'customer_email' => 'robert.t@example.com',
                'customer_phone' => null,
                'category_id' => 3,
            ],
            [
                'subject' => 'Payment processing failed',
                'description' => 'My payment was declined but the amount was deducted from my account. Transaction ID: TXN123456789. Please help resolve this urgently.',
                'priority' => 'urgent',
                'status' => 'open',
                'customer_name' => 'Amanda Anderson',
                'customer_email' => 'amanda.a@example.com',
                'customer_phone' => '+1234567895',
                'category_id' => 2,
            ],
            [
                'subject' => 'Account locked after multiple login attempts',
                'description' => 'My account got locked after I entered the wrong password a few times. How can I unlock it?',
                'priority' => 'high',
                'status' => 'resolved',
                'customer_name' => 'Christopher Lee',
                'customer_email' => 'chris.lee@example.com',
                'customer_phone' => '+1234567896',
                'category_id' => 1,
            ],
            [
                'subject' => 'Integration with third-party calendar',
                'description' => 'Is it possible to integrate your platform with Google Calendar? This would help me manage my schedule better.',
                'priority' => 'medium',
                'status' => 'pending',
                'customer_name' => 'Michelle White',
                'customer_email' => 'michelle.w@example.com',
                'customer_phone' => '+1234567897',
                'category_id' => 3,
            ],
        ];

        foreach ($tickets as $index => $ticketData) {
            $ticketNumber = 'TKT-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT);

            // Set resolved_at for resolved tickets
            $resolvedAt = null;
            if ($ticketData['status'] === 'resolved') {
                $resolvedAt = now()->subDays(rand(1, 5));
            }

            // Set closed_at for closed tickets
            $closedAt = null;
            if ($ticketData['status'] === 'closed') {
                $closedAt = now()->subDays(rand(1, 3));
                $resolvedAt = $closedAt->copy()->subDays(1);
            }

            DB::table('tickets')->insert([
                'company_id' => 1,
                'ticket_number' => $ticketNumber,
                'customer_name' => $ticketData['customer_name'],
                'customer_email' => $ticketData['customer_email'],
                'customer_phone' => $ticketData['customer_phone'],
                'subject' => $ticketData['subject'],
                'description' => $ticketData['description'],
                'status' => $ticketData['status'],
                'priority' => $ticketData['priority'],
                'assigned_to' => 2,
                'category_id' => $ticketData['category_id'],
                'verified' => true,
                'verification_token' => null,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(0, 10)),
                'resolved_at' => $resolvedAt,
                'closed_at' => $closedAt,
            ]);
        }

        $this->command->info('10 tickets seeded successfully!');
    }
}
