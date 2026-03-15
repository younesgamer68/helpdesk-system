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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Allow multiple users with the same email across DIFFERENT companies
            $table->unique(['company_id', 'email']);
        });

        // Add a foreign key to the tickets table pointing to the new customers table
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
        });

        // Migrate existing customers from the tickets table
        DB::table('tickets')->orderBy('id')->chunk(100, function ($tickets) {
            $existingCompanyIds = DB::table('companies')->pluck('id')->toArray();

            foreach ($tickets as $ticket) {
                // Skip if the ticket's company no longer exists
                if (! in_array($ticket->company_id, $existingCompanyIds)) {
                    continue;
                }

                // Find or create the customer for this company and email
                $customer = DB::table('customers')
                    ->where('company_id', $ticket->company_id)
                    ->where('email', $ticket->customer_email)
                    ->first();

                if (! $customer) {
                    $customerId = DB::table('customers')->insertGetId([
                        'company_id' => $ticket->company_id,
                        'name' => $ticket->customer_name,
                        'email' => $ticket->customer_email,
                        'phone' => $ticket->customer_phone,
                        'is_active' => true,
                        'created_at' => $ticket->created_at,
                        'updated_at' => $ticket->created_at,
                    ]);
                } else {
                    $customerId = $customer->id;
                }

                // Update the ticket to point to this customer
                DB::table('tickets')
                    ->where('id', $ticket->id)
                    ->update(['customer_id' => $customerId]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });

        Schema::dropIfExists('customers');
    }
};
