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
        Schema::table('ai_suggestion_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('ai_suggestion_logs', 'suggestion_text')) {
                $table->text('suggestion_text')->nullable()->after('action');
            }
            if (! Schema::hasColumn('ai_suggestion_logs', 'edited_text')) {
                $table->text('edited_text')->nullable()->after('suggestion_text');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_suggestion_logs', function (Blueprint $table) {
            $table->dropColumn(['suggestion_text', 'edited_text']);
        });
    }
};
