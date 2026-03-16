<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('tickets', function (Blueprint $table) {
                $table->fullText(['subject', 'description'], 'tickets_search_fulltext');
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropFullText('tickets_search_fulltext');
            });
        }
    }
};
