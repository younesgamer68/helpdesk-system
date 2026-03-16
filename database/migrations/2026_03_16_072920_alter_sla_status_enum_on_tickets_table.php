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
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('tickets', function (Blueprint $table) {
            $table->enum('sla_status', ['on_time', 'at_risk', 'breached'])
                ->default('on_time')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('sla_status')
                ->default('on_time')
                ->change();
        });
    }
};
