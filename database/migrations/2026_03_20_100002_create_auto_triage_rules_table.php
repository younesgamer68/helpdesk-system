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
        if (! Schema::hasTable('auto_triage_rules')) {
            Schema::create('auto_triage_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('type')->default('keyword'); // keyword or ai
                $table->json('keywords')->nullable();
                $table->foreignId('category_id')->nullable()->constrained('ticket_categories')->nullOnDelete();
                $table->string('priority')->nullable(); // low, medium, high, urgent
                $table->boolean('is_active')->default(true);
                $table->integer('order')->default(0);
                $table->timestamps();
                $table->index(['company_id', 'is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_triage_rules');
    }
};
