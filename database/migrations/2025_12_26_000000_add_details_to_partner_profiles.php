<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_profiles', function (Blueprint $table) {
            // General Info
            if (!Schema::hasColumn('partner_profiles', 'company_type')) {
                $table->string('company_type')->nullable(); // Agency, Freelancer, Recruiter
            }
            if (!Schema::hasColumn('partner_profiles', 'profile_picture_path')) {
                $table->string('profile_picture_path')->nullable();
            }
            if (!Schema::hasColumn('partner_profiles', 'establishment_year')) {
                $table->integer('establishment_year')->nullable();
            }
            if (!Schema::hasColumn('partner_profiles', 'bio')) {
                $table->text('bio')->nullable();
            }
            if (!Schema::hasColumn('partner_profiles', 'address')) {
                $table->text('address')->nullable();
            }
            if (!Schema::hasColumn('partner_profiles', 'website')) {
                $table->string('website')->nullable();
            }
            if (!Schema::hasColumn('partner_profiles', 'working_hours')) {
                $table->string('working_hours')->nullable();
            }
            if (!Schema::hasColumn('partner_profiles', 'preferred_locations')) {
                $table->string('preferred_locations')->nullable();
            }
            if (!Schema::hasColumn('partner_profiles', 'preferred_categories')) {
                $table->string('preferred_categories')->nullable();
            }

            // Social Links
            if (!Schema::hasColumn('partner_profiles', 'linkedin_url')) {
                $table->string('linkedin_url')->nullable();
            }
            if (!Schema::hasColumn('partner_profiles', 'facebook_url')) {
                $table->string('facebook_url')->nullable();
            }
            if (!Schema::hasColumn('partner_profiles', 'twitter_url')) {
                $table->string('twitter_url')->nullable();
            }
            if (!Schema::hasColumn('partner_profiles', 'instagram_url')) {
                $table->string('instagram_url')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('partner_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'company_type', 'profile_picture_path', 'establishment_year', 'bio', 'address', 'website',
                'working_hours', 'preferred_locations', 'preferred_categories',
                'linkedin_url', 'facebook_url', 'twitter_url', 'instagram_url'
            ]);
        });
    }
};