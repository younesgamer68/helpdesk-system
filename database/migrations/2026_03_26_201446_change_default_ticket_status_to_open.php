<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->enum('status', ['pending', 'open', 'in_progress', 'resolved', 'closed'])
                ->default('open')
                ->change();
        });

        Schema::table('widget_settings', function (Blueprint $table) {
            $table->enum('default_status', ['pending', 'open'])
                ->default('open')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->enum('status', ['pending', 'open', 'in_progress', 'resolved', 'closed'])
                ->default('pending')
                ->change();
        });

        Schema::table('widget_settings', function (Blueprint $table) {
            $table->enum('default_status', ['pending', 'open'])
                ->default('pending')
                ->change();
        });
    }
};
