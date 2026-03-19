<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sla_policies', function (Blueprint $table) {
            $table->unsignedInteger('warning_hours')->default(24)->after('urgent_minutes');
            $table->unsignedInteger('auto_close_hours')->default(48)->after('warning_hours');
            $table->unsignedInteger('reopen_hours')->default(48)->after('auto_close_hours');
            $table->unsignedInteger('linked_ticket_days')->default(7)->after('reopen_hours');
            $table->unsignedInteger('soft_delete_days')->default(30)->after('linked_ticket_days');
            $table->unsignedInteger('hard_delete_days')->default(90)->after('soft_delete_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sla_policies', function (Blueprint $table) {
            $table->dropColumn([
                'warning_hours', 'auto_close_hours', 'reopen_hours',
                'linked_ticket_days', 'soft_delete_days', 'hard_delete_days',
            ]);
        });
    }
};
