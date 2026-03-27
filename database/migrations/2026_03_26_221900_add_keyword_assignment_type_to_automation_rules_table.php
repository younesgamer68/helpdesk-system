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
        Schema::table('automation_rules', function (Blueprint $table) {
            $table->enum('type', ['assignment', 'keyword_assignment', 'priority', 'auto_reply', 'escalation', 'sla_breach'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('automation_rules', function (Blueprint $table) {
            $table->enum('type', ['assignment', 'priority', 'auto_reply', 'escalation', 'sla_breach'])->change();
        });
    }
};
