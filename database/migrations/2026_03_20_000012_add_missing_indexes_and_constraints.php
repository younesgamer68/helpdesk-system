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
        // ticket_replies composite index
        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->index(['ticket_id', 'is_internal', 'created_at'], 'tr_ticket_internal_created_idx');
            $table->softDeletes();
        });

        // kb_categories company index
        Schema::table('kb_categories', function (Blueprint $table) {
            $table->index('company_id', 'kb_cat_company_idx');
        });

        // kb_articles composite indexes
        Schema::table('kb_articles', function (Blueprint $table) {
            $table->index(['company_id', 'status'], 'kb_art_company_status_idx');
            $table->index(['company_id', 'kb_category_id'], 'kb_art_company_cat_idx');
            $table->foreignId('author_id')->nullable()->after('kb_category_id')
                ->constrained('users')->nullOnDelete();
        });

        // ticket_categories soft deletes
        Schema::table('ticket_categories', function (Blueprint $table) {
            $table->softDeletes();
        });

        // customers soft deletes
        Schema::table('customers', function (Blueprint $table) {
            $table->softDeletes();
            $table->text('notes')->nullable()->after('phone');
        });

        // notifications compound index
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(
                ['notifiable_type', 'notifiable_id', 'read_at'],
                'notif_notifiable_read_idx'
            );
        });

        // sla_policies UNIQUE company_id
        Schema::table('sla_policies', function (Blueprint $table) {
            $table->boolean('business_hours_only')->default(false)->after('is_enabled');
            $table->unique('company_id', 'sla_policies_company_id_unique');
        });

        // sla_policy_rules table
        Schema::create('sla_policy_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained('sla_policies')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('ticket_categories')->nullOnDelete();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->nullable();
            $table->unsignedInteger('response_minutes');
            $table->unsignedInteger('resolution_minutes')->nullable();
            $table->timestamps();
            $table->index(['policy_id', 'category_id', 'priority'], 'sla_rules_lookup_idx');
        });

        // tickets composite indexes
        Schema::table('tickets', function (Blueprint $table) {
            $table->index(['company_id', 'status', 'assigned_to'], 'tickets_company_status_agent_idx');
            $table->index(['company_id', 'created_at'], 'tickets_company_created_idx');
            $table->index(['company_id', 'due_time', 'sla_status'], 'tickets_company_sla_idx');
        });

        // email_verification_codes
        Schema::table('email_verification_codes', function (Blueprint $table) {
            $table->index(['user_id', 'expires_at'], 'evc_user_expires_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->dropIndex('tr_ticket_internal_created_idx');
            $table->dropSoftDeletes();
        });

        Schema::table('kb_categories', function (Blueprint $table) {
            $table->dropIndex('kb_cat_company_idx');
        });

        Schema::table('kb_articles', function (Blueprint $table) {
            $table->dropIndex('kb_art_company_status_idx');
            $table->dropIndex('kb_art_company_cat_idx');
            $table->dropForeign(['author_id']);
            $table->dropColumn('author_id');
        });

        Schema::table('ticket_categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('notes');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notif_notifiable_read_idx');
        });

        Schema::table('sla_policies', function (Blueprint $table) {
            $table->dropColumn('business_hours_only');
            $table->dropUnique('sla_policies_company_id_unique');
        });

        Schema::dropIfExists('sla_policy_rules');

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('tickets_company_status_agent_idx');
            $table->dropIndex('tickets_company_created_idx');
            $table->dropIndex('tickets_company_sla_idx');
        });

        Schema::table('email_verification_codes', function (Blueprint $table) {
            $table->dropIndex('evc_user_expires_idx');
        });
    }
};
