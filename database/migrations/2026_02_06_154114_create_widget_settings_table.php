<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widget_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('widget_key', 64)->unique();
            $table->boolean('is_active')->default(true);

            // Appearance settings
            $table->string('primary_color', 7)->default('#14b8a6');
            $table->string('form_title')->default('Submit a Support Ticket');
            $table->text('welcome_message')->nullable();
            $table->text('success_message')->default('Thank you! Please check your email to verify your ticket.');

            // Form field settings
            $table->boolean('require_phone')->default(false);
            $table->boolean('show_category')->default(true);

            // Default ticket settings
            $table->foreignId('default_assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('default_status', ['pending', 'open'])->default('pending');
            $table->enum('default_priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            $table->timestamps();

            // Indexes
            $table->index('company_id');
            $table->index('widget_key');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_settings');
    }
};
