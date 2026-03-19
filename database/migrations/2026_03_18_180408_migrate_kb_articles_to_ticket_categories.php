<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds ticket_category_id to kb_articles and migrates data from kb_category_id.
     * The old kb_category_id column is kept for now for safety during transition.
     */
    public function up(): void
    {
        // Add ticket_category_id column if it doesn't exist
        if (! Schema::hasColumn('kb_articles', 'ticket_category_id')) {
            Schema::table('kb_articles', function (Blueprint $table) {
                $table->foreignId('ticket_category_id')->nullable()->after('body')->constrained('ticket_categories')->nullOnDelete();
            });
        }

        // Only migrate data if kb_category_id exists and has values
        if (Schema::hasColumn('kb_articles', 'kb_category_id')) {
            // Migrate data from kb_category_id to ticket_category_id
            // Try to match by name when possible, fall back to company default category
            try {
                \DB::table('kb_articles')
                    ->whereNotNull('kb_category_id')
                    ->each(function ($article) {
                        $kbCategory = \DB::table('kb_categories')->find($article->kb_category_id);
                        if ($kbCategory) {
                            // Try to find matching ticket category by name
                            $ticketCategory = \DB::table('ticket_categories')
                                ->where('company_id', $kbCategory->company_id)
                                ->where('name', $kbCategory->name)
                                ->first();

                            if (! $ticketCategory) {
                                // Fall back to first category for this company
                                $ticketCategory = \DB::table('ticket_categories')
                                    ->where('company_id', $kbCategory->company_id)
                                    ->first();
                            }

                            if ($ticketCategory) {
                                \DB::table('kb_articles')
                                    ->where('id', $article->id)
                                    ->update(['ticket_category_id' => $ticketCategory->id]);
                            }
                        }
                    });
            } catch (\Exception $e) {
                // Log the error but continue - manual migration may be needed
                \Log::error('KB category migration error: '.$e->getMessage());
            }
        }

        // Create index for the new column
        try {
            Schema::table('kb_articles', function (Blueprint $table) {
                $table->index(['company_id', 'ticket_category_id'], 'kb_art_company_ticket_cat_idx');
            });
        } catch (\Exception $e) {
            // Index might already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the new column
        if (Schema::hasColumn('kb_articles', 'ticket_category_id')) {
            try {
                Schema::table('kb_articles', function (Blueprint $table) {
                    if (Schema::hasIndex('kb_articles', 'kb_art_company_ticket_cat_idx')) {
                        $table->dropIndex('kb_art_company_ticket_cat_idx');
                    }
                    $table->dropForeign(['ticket_category_id']);
                    $table->dropColumn('ticket_category_id');
                });
            } catch (\Exception $e) {
                // Column might not exist or foreign key might not be named as expected
            }
        }
    }
};
