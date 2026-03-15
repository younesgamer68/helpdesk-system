<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tickets = App\Models\Ticket::whereIn('assigned_to', [26, 27])->get(['id', 'ticket_number', 'subject', 'assigned_to', 'category_id', 'company_id']);
echo "Tickets assigned to 26 (jalal) or 27 (wow):\n";
foreach ($tickets as $t) {
    echo "ID: {$t->id} | Num: {$t->ticket_number} | Subject: {$t->subject} | Assigned: {$t->assigned_to}\n";
}

$tickets2 = App\Models\Ticket::where('subject', 'like', '%serveur est en panne%')->get(['id', 'ticket_number', 'subject', 'assigned_to', 'category_id', 'company_id']);
echo "\nTickets matching subject:\n";
foreach ($tickets2 as $t) {
    echo "ID: {$t->id} | Num: {$t->ticket_number} | Subject: {$t->subject} | Assigned: {$t->assigned_to} | Cat: {$t->category_id} | Comp: {$t->company_id}\n";
}
