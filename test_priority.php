<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AutomationRule;
use App\Models\Ticket;
use App\Services\Automation\Rules\PriorityRule;
use App\Models\Company;

$company = Company::factory()->create();

// Create an automation rule that sets priority to urgent if it's currently medium
$rule = AutomationRule::create([
    'company_id' => $company->id,
    'name' => 'Set to Urgent',
    'type' => AutomationRule::TYPE_PRIORITY,
    'conditions' => [
        'current_priority' => ['medium', 'low']
    ],
    'actions' => [
        'set_priority' => 'urgent'
    ],
    'is_active' => true,
    'priority' => 1,
]);

$ticket = Ticket::factory()->create([
    'company_id' => $company->id,
    'status' => 'open',
    'priority' => 'medium'
]);

echo "Initial priority: " . $ticket->priority . "\n";

$priorityRule = app(PriorityRule::class);
$evaluated = $priorityRule->evaluate($rule, $ticket);
echo "Evaluated: " . ($evaluated ? 'Yes' : 'No') . "\n";

if ($evaluated) {
    dump("Current Ticket Before Apply: " . json_encode($ticket->toArray()));
    $priorityRule->apply($rule, $ticket);
    echo "Priority after apply: " . $ticket->fresh()->priority . "\n";
}
else {
    echo "Rule condition failed.\n";
}

$rule->delete();
$ticket->delete();
$company->delete();
