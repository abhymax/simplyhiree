<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_profiles', function (Blueprint $table) {
            // Profile Header
            $table->string('profile_picture_path')->nullable()->after('user_id');
            $table->string('company_type')->nullable()->after('profile_picture_path'); // Freelancer/Individual vs Company
            $table->string('website')->nullable()->after('company_type');
            $table->string('establishment_year')->nullable()->after('website');

            // Preferences
            $table->text('preferred_categories')->nullable()->after('establishment_year'); // Stored as text/JSON for flexibility
            $table->string('preferred_locations')->nullable()->after('preferred_categories');

            // Bio & Contact
            $table->text('bio')->nullable()->after('preferred_locations');
            $table->text('address')->nullable()->after('bio');
            $table->string('working_hours')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('partner_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'profile_picture_path', 
                'company_type', 
                'website', 
                'establishment_year', 
                'preferred_categories', 
                'preferred_locations', 
                'bio', 
                'address', 
                'working_hours'
            ]);
        });
    }
};