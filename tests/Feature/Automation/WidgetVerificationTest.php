<?php

namespace Tests\Feature\Automation;

use App\Models\AutomationRule;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WidgetVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_automation_rules_run_after_verification()
    {
        // 1. Setup Company and Category
        $company = Company::factory()->create();
        $category = TicketCategory::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create(['company_id' => $company->id]);

        // 2. Create Automation Rule (Priority High if subject contains 'Urgent')
        AutomationRule::create([
            'company_id' => $company->id,
            'name' => 'Urgent Keyword',
            'type' => 'priority',
            'is_active' => true,
            'conditions' => [
                'keywords' => ['urgent'], 
                'category_id' => $category->id,
                'current_priority' => []
            ],
            'actions' => ['set_priority' => 'high'],
        ]);

        // 3. Create Unverified Ticket via Widget simulation
        $ticket = Ticket::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'ticket_number' => 'TKT-TEST01',
            'subject' => 'URGENT - Server Down',
            'description' => 'Help me',
            'category_id' => $category->id,
            'priority' => 'medium', // Default
            'status' => 'pending',
            'verified' => false,
            'verification_token' => 'token123',
            'source' => 'widget',
        ]);

        // Assert initial state
        $this->assertEquals('medium', $ticket->fresh()->priority);

        // 4. Simulate WidgetController::verify logic
        // First generic update setting verified = true
        $ticket->update([
            'verified' => true,
            'verification_token' => null,
        ]);

        // Second update setting tracking token
        $ticket->update(['tracking_token' => 'track123']);

        // 5. Assert Final State
        // Automation should have run during the FIRST update
        $this->assertEquals('high', $ticket->fresh()->priority, 'Priority should be updated to high after verification.');
    }
}
