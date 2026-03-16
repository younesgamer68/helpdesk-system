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
        Schema::table('agent_conversations', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('user_id');
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->index(['company_id', 'user_id', 'updated_at'], 'agent_conv_company_user_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_conversations', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex('agent_conv_company_user_idx');
            $table->dropColumn('company_id');
        });
    }
};
