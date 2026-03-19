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
        Schema::table('company_ai_settings', function (Blueprint $table) {
            $table->string('escalation_url_type', 20)->default('standalone')->after('chatbot_fallback_threshold');
            $table->string('custom_escalation_url', 2048)->nullable()->after('escalation_url_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_ai_settings', function (Blueprint $table) {
            $table->dropColumn(['escalation_url_type', 'custom_escalation_url']);
        });
    }
};
