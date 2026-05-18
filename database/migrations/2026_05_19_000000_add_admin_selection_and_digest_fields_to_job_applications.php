<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('job_applications', 'selected_by_admin_id')) {
                $table->unsignedBigInteger('selected_by_admin_id')->nullable()->after('client_notes')
                    ->comment('Superadmin user id who marked Selected on behalf of the client');
                $table->index('selected_by_admin_id');
            }
            if (!Schema::hasColumn('job_applications', 'selected_by_admin_at')) {
                $table->timestamp('selected_by_admin_at')->nullable()->after('selected_by_admin_id');
            }
            if (!Schema::hasColumn('job_applications', 'approved_digest_sent_at')) {
                $table->timestamp('approved_digest_sent_at')->nullable()->after('selected_by_admin_at')
                    ->comment('When this approved application was emailed to the client in the morning digest');
                $table->index('approved_digest_sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            foreach (['selected_by_admin_id', 'selected_by_admin_at', 'approved_digest_sent_at'] as $col) {
                if (Schema::hasColumn('job_applications', $col)) {
                    if ($col !== 'selected_by_admin_at') {
                        try { $table->dropIndex([$col]); } catch (\Throwable $e) {}
                    }
                    $table->dropColumn($col);
                }
            }
        });
    }
};
