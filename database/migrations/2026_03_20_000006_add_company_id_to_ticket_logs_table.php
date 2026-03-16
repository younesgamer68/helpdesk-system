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
        Schema::table('ticket_logs', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')
                ->constrained()->cascadeOnDelete();
            $table->index(['company_id', 'created_at']);
            $table->index(['ticket_id', 'created_at']);
        });

        // Backfill company_id from the ticket
        DB::statement('
            UPDATE ticket_logs
            SET company_id = (
                SELECT company_id FROM tickets WHERE tickets.id = ticket_logs.ticket_id
            )
            WHERE company_id IS NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_logs', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id', 'created_at']);
            $table->dropIndex(['ticket_id', 'created_at']);
            $table->dropColumn('company_id');
        });
    }
};
