<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$company = App\Models\Company::where('name', 'Test Company')->first();
$category = App\Models\TicketCategory::where('name', 'Billing')->first();
$ticket = App\Models\Ticket::factory()->create([
    'company_id' => $company->id,
    'category_id' => $category->id,
    'assigned_to' => null,
    'status' => 'open'
]);

$service = app(App\Services\TicketAssignmentService::class);
$assigned = $service->assignTicket($ticket);

echo "Assigned to: " . ($assigned ? $assigned->name : 'Non assigné') . "\n";
if ($assigned) {
    echo "Tickets ouverts (selon assignation): " . $assigned->assigned_tickets_count . "\n";
}
