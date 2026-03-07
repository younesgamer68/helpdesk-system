<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First update existing data
        DB::table('users')->where('role', 'technician')->update(['role' => 'operator']);

        // Now update the schema (SQLite supports this natively in Laravel 11+)
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'operator'])->default('admin')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'technician'])->default('admin')->change();
        });

        DB::table('users')->where('role', 'operator')->update(['role' => 'technician']);
    }
};
