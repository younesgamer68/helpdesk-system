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
            if (! Schema::hasColumn('companies', 'timezone')) {
                $table->string('timezone')->nullable()->after('require_client_verification');
            }
            $table->timestamp('onboarding_completed_at')->nullable()->after('require_client_verification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'timezone')) {
                $table->dropColumn('timezone');
            }
            $table->dropColumn('onboarding_completed_at');
        });
    }
};
