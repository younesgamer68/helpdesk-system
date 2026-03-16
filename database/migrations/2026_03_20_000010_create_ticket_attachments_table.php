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
        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_reply_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');         // original filename
            $table->string('path')->unique(); // storage path
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('size')->nullable(); // bytes
            $table->timestamps();
            $table->index('ticket_id');
            $table->index('ticket_reply_id');
        });

        // Backfill from existing JSON on ticket_replies
        DB::table('ticket_replies')
            ->whereNotNull('attachments')
            ->where('attachments', '!=', 'null')
            ->where('attachments', '!=', '[]')
            ->orderBy('id')
            ->chunk(100, function ($replies) {
                foreach ($replies as $reply) {
                    $attachments = json_decode($reply->attachments, true);
                    if (! is_array($attachments)) {
                        continue;
                    }
                    foreach ($attachments as $att) {
                        if (empty($att['path'])) {
                            continue;
                        }
                        DB::table('ticket_attachments')->insertOrIgnore([
                            'ticket_id' => $reply->ticket_id,
                            'ticket_reply_id' => $reply->id,
                            'name' => $att['name'] ?? basename($att['path']),
                            'path' => $att['path'],
                            'mime_type' => $att['mime_type'] ?? null,
                            'size' => $att['size'] ?? null,
                            'created_at' => $reply->created_at,
                            'updated_at' => $reply->updated_at,
                        ]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_attachments');
    }
};
