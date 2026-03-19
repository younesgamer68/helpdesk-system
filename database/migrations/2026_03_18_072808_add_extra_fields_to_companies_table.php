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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('website')->nullable()->after('email');
            $table->string('kb_subdomain')->nullable()->unique()->after('website');
            $table->string('accent_color', 7)->nullable()->default('#0B4F4A')->after('kb_subdomain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['website', 'kb_subdomain', 'accent_color']);
        });
    }
};
