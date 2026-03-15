<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::all(['id', 'name', 'role', 'specialty_id']);
$categories = App\Models\TicketCategory::all(['id', 'name']);

$output = "USERS:\n";
foreach ($users as $user) {
    $specialty = $user->specialty_id ? ($categories->firstWhere('id', $user->specialty_id)->name ?? 'Unknown') : 'None';
    $output .= "ID: {$user->id} | Name: {$user->name} | Role: {$user->role} | Specialty: {$specialty} ({$user->specialty_id})\n";
}

$output .= "\nCATEGORIES:\n";
foreach ($categories as $cat) {
    $output .= "ID: {$cat->id} | Name: {$cat->name}\n";
}

file_put_contents('user_data_debug.txt', $output);
echo "Done. Check user_data_debug.txt";
