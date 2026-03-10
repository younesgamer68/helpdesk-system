<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(27); // wow
Auth::login($user);

$query = App\Models\Ticket::where('company_id', $user->company_id)->where('verified', 1)
    ->with(['user:id,name', 'category:id,name']);

if ($user->role !== 'admin') {
    if ($user->specialty_id) {
        $query->where(function ($q) use ($user) {
            $q->where(function ($subQ) use ($user) {
                    $subQ->where('category_id', $user->specialty_id)
                        ->whereNull('assigned_to');
                }
                )->orWhere('assigned_to', $user->id);
            });
    }
    else {
        $query->where('assigned_to', $user->id);
    }
}

$output = "SQL: " . $query->toSql() . "\n";
$output .= "Bindings: " . json_encode($query->getBindings()) . "\n";

$tickets = $query->get();
$output .= "Tickets found for wow:\n";
foreach ($tickets as $t) {
    $output .= "ID: {$t->id} | Assigned To: {$t->assigned_to}\n";
}

file_put_contents('output_query.txt', $output);
echo "Written to output_query.txt";
