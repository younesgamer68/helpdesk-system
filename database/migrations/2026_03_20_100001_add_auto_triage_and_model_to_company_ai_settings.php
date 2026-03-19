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
        if (! Schema::hasColumn('company_ai_settings', 'ai_auto_triage_enabled')) {
            Schema::table('company_ai_settings', function (Blueprint $table) {
                $table->boolean('ai_auto_triage_enabled')->default(false)->after('ai_chatbot_enabled');
            });
        }

        if (! Schema::hasColumn('company_ai_settings', 'ai_model')) {
            Schema::table('company_ai_settings', function (Blueprint $table) {
                $table->string('ai_model')->default('gemini-2.5-flash')->after('ai_auto_triage_enabled');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columnsToDrop = [];

        if (Schema::hasColumn('company_ai_settings', 'ai_auto_triage_enabled')) {
            $columnsToDrop[] = 'ai_auto_triage_enabled';
        }

        if (Schema::hasColumn('company_ai_settings', 'ai_model')) {
            $columnsToDrop[] = 'ai_model';
        }

        if ($columnsToDrop !== []) {
            Schema::table('company_ai_settings', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }
};
