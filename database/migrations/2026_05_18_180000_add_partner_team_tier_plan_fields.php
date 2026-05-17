<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'parent_partner_id')) {
                $table->unsignedBigInteger('parent_partner_id')->nullable()->after('id')
                    ->comment('If set, this user is a team member under that partner-owner user');
                $table->index('parent_partner_id');
            }
            if (!Schema::hasColumn('users', 'team_role')) {
                $table->string('team_role', 30)->nullable()->after('parent_partner_id')
                    ->comment('Recruiter | Manager (Owner has parent_partner_id = null)');
            }
            if (!Schema::hasColumn('users', 'access_level')) {
                $table->string('access_level', 30)->nullable()->after('team_role')
                    ->comment('full | submissions_only | view_only');
            }
            if (!Schema::hasColumn('users', 'partner_tier')) {
                $table->string('partner_tier', 20)->default('Bronze')->after('access_level')
                    ->comment('Bronze | Silver | Gold | Diamond');
            }
            if (!Schema::hasColumn('users', 'partner_plan')) {
                $table->string('partner_plan', 20)->default('Free')->after('partner_tier')
                    ->comment('Free | Pro | Premium');
            }
        });

        Schema::table('job_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('job_applications', 'submitted_by_user_id')) {
                $table->unsignedBigInteger('submitted_by_user_id')->nullable()->after('candidate_id')
                    ->comment('The team-member partner user who actually submitted this application');
                $table->index('submitted_by_user_id');
            }
        });

        Schema::table('jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('jobs', 'is_premium')) {
                $table->boolean('is_premium')->default(false)->after('partner_visibility')
                    ->comment('Premium jobs are only visible/applicable to Pro/Premium-plan partners');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['parent_partner_id','team_role','access_level','partner_tier','partner_plan'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    if ($col === 'parent_partner_id') $table->dropIndex(['parent_partner_id']);
                    $table->dropColumn($col);
                }
            }
        });
        Schema::table('job_applications', function (Blueprint $table) {
            if (Schema::hasColumn('job_applications', 'submitted_by_user_id')) {
                $table->dropIndex(['submitted_by_user_id']);
                $table->dropColumn('submitted_by_user_id');
            }
        });
        Schema::table('jobs', function (Blueprint $table) {
            if (Schema::hasColumn('jobs', 'is_premium')) $table->dropColumn('is_premium');
        });
    }
};
