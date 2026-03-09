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
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');

            // Rule identification
            $table->string('name');
            $table->text('description')->nullable();

            // Rule type
            $table->enum('type', ['assignment', 'priority', 'auto_reply', 'escalation']);

            // Conditions and actions stored as JSON
            $table->json('conditions');
            $table->json('actions');

            // Rule status and ordering
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('priority')->default(0);

            // Execution tracking
            $table->unsignedBigInteger('executions_count')->default(0);
            $table->timestamp('last_executed_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['company_id', 'is_active', 'type']);
            $table->index(['company_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_rules');
    }
};
