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
        Schema::table('kb_categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('kb_categories')->nullOnDelete()->after('id');
            $table->integer('order')->default(0)->after('description');
            $table->string('icon')->nullable()->after('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kb_categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'order', 'icon']);
        });
    }
};
