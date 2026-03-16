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
        if (! Schema::hasTable('company_ai_settings')) {
            Schema::create('company_ai_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
                $table->boolean('ai_suggestions_enabled')->default(false);
                $table->boolean('ai_summary_enabled')->default(false);
                $table->boolean('ai_chatbot_enabled')->default(false);
                $table->text('chatbot_greeting')->nullable();
                $table->float('chatbot_fallback_threshold')->default(0.5);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_ai_settings');
    }
};
