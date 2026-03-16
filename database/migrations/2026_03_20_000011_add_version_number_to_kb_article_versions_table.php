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
        Schema::table('kb_article_versions', function (Blueprint $table) {
            $table->unsignedInteger('version_number')->default(1)->after('kb_article_id');
            $table->index(['kb_article_id', 'version_number']);
        });

        // Backfill sequential version numbers per article
        $articles = DB::table('kb_article_versions')
            ->select('kb_article_id')
            ->distinct()
            ->pluck('kb_article_id');

        foreach ($articles as $articleId) {
            $versions = DB::table('kb_article_versions')
                ->where('kb_article_id', $articleId)
                ->orderBy('created_at')
                ->pluck('id');

            foreach ($versions as $i => $id) {
                DB::table('kb_article_versions')
                    ->where('id', $id)
                    ->update(['version_number' => $i + 1]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kb_article_versions', function (Blueprint $table) {
            $table->dropIndex(['kb_article_id', 'version_number']);
            $table->dropColumn('version_number');
        });
    }
};
