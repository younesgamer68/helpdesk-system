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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('require_client_verification')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('slug'); // Already unique, but explicit index for clarity
            $table->index('email'); // For lookups by email
            $table->index('created_at'); // For sorting by creation date
            $table->index(['require_client_verification', 'created_at']); // Composite for filtered queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
