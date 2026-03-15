<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('tickets', function (Blueprint $table) {
    if (Schema::hasColumn('tickets', 'customer_id')) {
        // SQLite might have issues dropping foreign keys directly depending on version, 
        // but we'll try or just drop the column
        try {
            $table->dropForeign(['customer_id']);
        }
        catch (\Exception $e) {
        }
        try {
            $table->dropColumn('customer_id');
        }
        catch (\Exception $e) {
        }
    }
});

Schema::dropIfExists('customers');
echo "Cleanup done.\n";
