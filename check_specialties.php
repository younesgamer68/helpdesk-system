<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$users = User::whereIn('name', ['jalal', 'wow'])->get();

foreach ($users as $user) {
    echo "User: " . $user->name . "\n";
    echo "Role: " . $user->role . "\n";
    echo "Specialty ID: " . ($user->specialty_id ?: 'None') . "\n";
    if ($user->specialty_id) {
        $category = \App\Models\TicketCategory::find($user->specialty_id);
        echo "Specialty Name: " . ($category ? $category->name : 'Unknown') . "\n";
    }
    echo "-------------------\n";
}
