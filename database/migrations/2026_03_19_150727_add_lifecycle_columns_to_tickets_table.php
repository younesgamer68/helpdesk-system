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
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('warning_sent_at')->nullable()->after('closed_at');
            $table->string('close_reason')->nullable()->after('warning_sent_at');
            $table->foreignId('parent_ticket_id')->nullable()->after('close_reason')
                ->constrained('tickets')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['parent_ticket_id']);
            $table->dropColumn(['warning_sent_at', 'close_reason', 'parent_ticket_id']);
        });
    }
};
