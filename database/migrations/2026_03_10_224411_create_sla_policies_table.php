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
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->integer('low_minutes')->default(1440); // 24 hours
            $table->integer('medium_minutes')->default(480); // 8 hours
            $table->integer('high_minutes')->default(120); // 2 hours
            $table->integer('urgent_minutes')->default(30); // 30 minutes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sla_policies');
    }
};
