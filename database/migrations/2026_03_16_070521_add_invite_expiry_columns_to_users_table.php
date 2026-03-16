<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('invite_sent_at')->nullable()->after('last_activity');
            $table->timestamp('invite_expires_at')->nullable()->after('invite_sent_at');
            $table->timestamp('invite_expired_notified_at')->nullable()->after('invite_expires_at');

            $table->index('invite_expires_at');
        });

        $invitationExpireHours = (int) config('auth.invitation_expire_hours', 72);

        DB::table('users')
            ->whereNull('password')
            ->whereNull('google_id')
            ->whereNull('invite_expires_at')
            ->orderBy('id')
            ->select(['id', 'created_at'])
            ->chunkById(200, function ($users) use ($invitationExpireHours): void {
                foreach ($users as $user) {
                    $sentAt = $user->created_at ? Carbon::parse($user->created_at) : now();

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'invite_sent_at' => $sentAt,
                            'invite_expires_at' => $sentAt->copy()->addHours($invitationExpireHours),
                            'invite_expired_notified_at' => null,
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['invite_expires_at']);
            $table->dropColumn([
                'invite_sent_at',
                'invite_expires_at',
                'invite_expired_notified_at',
            ]);
        });
    }
};
