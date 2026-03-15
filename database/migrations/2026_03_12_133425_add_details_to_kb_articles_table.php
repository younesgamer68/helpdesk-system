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
        Schema::table('kb_articles', function (Blueprint $table) {
            $table->string('meta_description')->nullable()->after('slug');
            $table->timestamp('schedule_publish_date')->nullable()->after('helpful_no');
            $table->timestamp('published_at')->nullable()->after('schedule_publish_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kb_articles', function (Blueprint $table) {
            $table->dropColumn(['meta_description', 'schedule_publish_date', 'published_at']);
        });
    }
};
