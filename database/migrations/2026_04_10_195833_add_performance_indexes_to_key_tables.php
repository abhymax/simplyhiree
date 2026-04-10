<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // job_applications — most queried table in the app
        Schema::table('job_applications', function (Blueprint $table) {
            $table->index('candidate_id');                          // partner's applicants
            $table->index('candidate_user_id');                    // direct applicants
            $table->index('assigned_partner_user_id');             // partner's pipeline
            $table->index(['hiring_status', 'joined_status']);     // hiring pipeline filters
            $table->index(['job_id', 'payment_status']);           // billing queries
            $table->index('interview_at');                         // daily schedule
        });

        // jobs
        Schema::table('jobs', function (Blueprint $table) {
            $table->index(['status', 'partner_visibility']);        // partner job listing
            $table->index(['user_id', 'status']);                  // client's jobs
            $table->index('category_id');                          // category filter
        });

        // candidates
        Schema::table('candidates', function (Blueprint $table) {
            $table->index('partner_id');                           // partner's candidate pool
        });

        // user_profiles
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->index('user_id');
        });

        // partner_profiles
        Schema::table('partner_profiles', function (Blueprint $table) {
            $table->index('user_id');
        });

        // client_profiles
        Schema::table('client_profiles', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropIndex(['candidate_id']);
            $table->dropIndex(['candidate_user_id']);
            $table->dropIndex(['assigned_partner_user_id']);
            $table->dropIndex(['hiring_status', 'joined_status']);
            $table->dropIndex(['job_id', 'payment_status']);
            $table->dropIndex(['interview_at']);
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndex(['status', 'partner_visibility']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['category_id']);
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropIndex(['partner_id']);
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('partner_profiles', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('client_profiles', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
    }
};
