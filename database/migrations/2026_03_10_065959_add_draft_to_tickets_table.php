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
            $table->text('draft_reply')->nullable()->after('closed_at');
            $table->text('draft_summary')->nullable()->after('draft_reply');
            $table->foreignId('draft_user_id')->nullable()->after('draft_summary')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['draft_user_id']);
            $table->dropColumn(['draft_reply', 'draft_summary', 'draft_user_id']);
        });
    }
};
