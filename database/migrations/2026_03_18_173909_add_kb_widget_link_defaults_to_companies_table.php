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
            $table->string('kb_widget_link_mode')->default('portal')->after('accent_color');
            $table->string('kb_widget_article_base_url')->nullable()->after('kb_widget_link_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['kb_widget_link_mode', 'kb_widget_article_base_url']);
        });
    }
};
