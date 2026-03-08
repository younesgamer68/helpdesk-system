<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Specialty: links operator to a ticket category they handle
            $table->foreignId('specialty_id')
                ->nullable()
                ->after('role')
                ->constrained('ticket_categories')
                ->onDelete('set null');

            // Availability: whether the operator is available for ticket assignment
            $table->boolean('is_available')
                ->default(true)
                ->after('specialty_id');

            // Track assigned ticket count for load balancing
            $table->unsignedInteger('assigned_tickets_count')
                ->default(0)
                ->after('is_available');

            // Index for faster queries
            $table->index(['specialty_id', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            $table->dropIndex(['specialty_id', 'is_available']);
            $table->dropColumn(['specialty_id', 'is_available', 'assigned_tickets_count']);
        });
    }
};
