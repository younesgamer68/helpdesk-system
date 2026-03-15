<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TicketAssignmentTestSeeder extends Seeder
{
    public function run()
    {
        $company = Company::firstOrCreate(
            ['name' => 'Test Company']
        );

        $billingCategory = TicketCategory::firstOrCreate([
            'company_id' => $company->id,
            'name' => 'Billing',
        ]);

        $generalCategory = TicketCategory::firstOrCreate([
            'company_id' => $company->id,
            'name' => 'General',
        ]);

        $technicalCategory = TicketCategory::firstOrCreate([
            'company_id' => $company->id,
            'name' => 'Technical',
        ]);

        $sara = User::updateOrCreate(
            ['email' => 'sara@test.com'],
            [
                'name' => 'Sara',
                'company_id' => $company->id,
                'role' => 'operator',
                'password' => Hash::make('password'),
                'specialty_id' => $billingCategory->id,
                'is_available' => true,
                'status' => 'online',
                'assigned_tickets_count' => 2,
            ]
        );

        $ahmed = User::updateOrCreate(
            ['email' => 'ahmed@test.com'],
            [
                'name' => 'Ahmed',
                'company_id' => $company->id,
                'role' => 'operator',
                'password' => Hash::make('password'),
                'specialty_id' => $technicalCategory->id,
                'is_available' => true,
                'status' => 'online',
                'assigned_tickets_count' => 1,
            ]
        );

        $karim = User::updateOrCreate(
            ['email' => 'karim@test.com'],
            [
                'name' => 'Karim',
                'company_id' => $company->id,
                'role' => 'operator',
                'password' => Hash::make('password'),
                'specialty_id' => $billingCategory->id,
                'is_available' => true,
                'status' => 'online',
                'assigned_tickets_count' => 3,
            ]
        );

        $fatima = User::updateOrCreate(
            ['email' => 'fatima@test.com'],
            [
                'name' => 'Fatima',
                'company_id' => $company->id,
                'role' => 'operator',
                'password' => Hash::make('password'),
                'specialty_id' => $generalCategory->id,
                'is_available' => false,
                'status' => 'offline',
                'assigned_tickets_count' => 0,
            ]
        );

        Ticket::whereIn('assigned_to', [$sara->id, $ahmed->id, $karim->id, $fatima->id])->delete();

        Ticket::factory()->count(2)->create([
            'company_id' => $company->id,
            'category_id' => $billingCategory->id,
            'assigned_to' => $sara->id,
            'status' => 'open',
        ]);

        Ticket::factory()->count(1)->create([
            'company_id' => $company->id,
            'category_id' => $technicalCategory->id,
            'assigned_to' => $ahmed->id,
            'status' => 'open',
        ]);

        Ticket::factory()->count(3)->create([
            'company_id' => $company->id,
            'category_id' => $billingCategory->id,
            'assigned_to' => $karim->id,
            'status' => 'open',
        ]);

        $this->command->info('Test data seeded for engine scenario. Agents created: Sara (Billing, 2), Ahmed (Technical, 1), Karim (Billing, 3), Fatima (General, 0).');
    }
}
