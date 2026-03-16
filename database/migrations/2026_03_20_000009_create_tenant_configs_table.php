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
        Schema::create('tenant_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('plan', ['starter', 'pro', 'enterprise'])->default('starter');
            $table->json('features')->nullable(); // {"ai": true, "sla": true, "kb": true}
            $table->json('limits')->nullable();   // {"max_agents": 10, "max_tickets_per_month": 1000}
            $table->string('db_connection')->nullable(); // null = shared DB, set = dedicated tenant DB
            $table->timestamps();
        });

        // Seed a default config for all existing companies
        DB::table('companies')->pluck('id')->each(function ($companyId) {
            DB::table('tenant_configs')->insertOrIgnore([
                'company_id' => $companyId,
                'plan' => 'starter',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_configs');
    }
};
