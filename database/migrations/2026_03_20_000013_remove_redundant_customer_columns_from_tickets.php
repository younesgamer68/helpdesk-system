<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Safety check — abort if any ticket lacks a customer_id
        $orphaned = DB::table('tickets')->whereNull('customer_id')->count();
        if ($orphaned > 0) {
            throw new \RuntimeException(
                "Cannot drop customer columns: {$orphaned} tickets have NULL customer_id. "
                .'Run the customer backfill migration first.'
            );
        }

        // Drop index first (required for SQLite) then drop remaining columns one-by-one
        if (Schema::hasColumn('tickets', 'customer_email')) {
            // The customer_email index may already be gone from a prior attempt
            try {
                Schema::table('tickets', function (Blueprint $table) {
                    $table->dropIndex(['customer_email']);
                });
            } catch (\Throwable) {
                // Index already dropped
            }
        }

        $columnsToDrop = array_filter(
            ['customer_name', 'customer_email', 'customer_phone'],
            fn ($col) => Schema::hasColumn('tickets', $col)
        );

        if (! empty($columnsToDrop)) {
            Schema::table('tickets', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('customer_name')->after('ticket_number');
            $table->string('customer_email')->after('customer_name');
            $table->string('customer_phone', 20)->nullable()->after('customer_email');
        });

        // Restore from customer relationship — SQLite-compatible approach
        DB::table('tickets')->whereNotNull('customer_id')->get()->each(function ($ticket) {
            $customer = DB::table('customers')->find($ticket->customer_id);
            if ($customer) {
                DB::table('tickets')->where('id', $ticket->id)->update([
                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                    'customer_phone' => $customer->phone,
                ]);
            }
        });
    }
};
