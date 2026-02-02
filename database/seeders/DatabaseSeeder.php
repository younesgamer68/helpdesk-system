<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Models\TicketCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
    }
}
