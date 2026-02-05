<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('ticket_number', 20)->unique();

            // Customer information (no user account)
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 20)->nullable();

            // Ticket details
            $table->string('subject', 500);
            $table->text('description');

            // Status and priority
            $table->enum('status', ['pending', 'open', 'in progress', 'resolved', 'closed'])
                ->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])
                ->default('medium');

            // Assignment and categorization
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained('ticket_categories')->onDelete('set null');

            // Email verification
            $table->boolean('verified')->default(false);
            $table->string('verification_token', 64)->nullable()->unique();

            // Timestamps
            $table->timestamps();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            // Indexes for performance
            $table->index('company_id');
            $table->index('ticket_number');
            $table->index('customer_email');
            $table->index('status');
            $table->index('priority');
            $table->index('assigned_to');
            $table->index('verified');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
