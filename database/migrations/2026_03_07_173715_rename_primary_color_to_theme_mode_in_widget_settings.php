<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->renameColumn('primary_color', 'theme_mode');
        });

        // Update existing values: any previous color value becomes 'dark' (default)
        \Illuminate\Support\Facades\DB::table('widget_settings')->update(['theme_mode' => 'dark']);
    }

    public function down(): void
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->renameColumn('theme_mode', 'primary_color');
        });

        \Illuminate\Support\Facades\DB::table('widget_settings')->update(['primary_color' => '#14b8a6']);
    }
};
