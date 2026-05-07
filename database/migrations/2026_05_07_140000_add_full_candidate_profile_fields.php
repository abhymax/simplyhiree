<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->json('preferred_locations')->nullable()->after('location');
            $table->unsignedTinyInteger('total_experience_years')->nullable()->after('experience_status');
            $table->unsignedTinyInteger('total_experience_months')->nullable()->after('total_experience_years');
            $table->string('current_company')->nullable()->after('total_experience_months');
            $table->string('current_designation')->nullable()->after('current_company');
            $table->string('current_ctc')->nullable()->after('current_designation');
            $table->string('marital_status', 30)->nullable()->after('gender');
            $table->string('qualification_degree')->nullable()->after('education_level');
            $table->string('specialization')->nullable()->after('qualification_degree');
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->json('preferred_locations')->nullable()->after('location');
            $table->unsignedTinyInteger('total_experience_years')->nullable()->after('experience_status');
            $table->unsignedTinyInteger('total_experience_months')->nullable()->after('total_experience_years');
            $table->string('current_company')->nullable()->after('total_experience_months');
            $table->string('current_ctc')->nullable()->after('current_role');
            $table->string('marital_status', 30)->nullable()->after('gender');
            $table->string('qualification_degree')->nullable();
            $table->string('specialization')->nullable()->after('qualification_degree');
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn([
                'preferred_locations', 'total_experience_years', 'total_experience_months',
                'current_company', 'current_designation', 'current_ctc',
                'marital_status', 'qualification_degree', 'specialization',
            ]);
        });
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'preferred_locations', 'total_experience_years', 'total_experience_months',
                'current_company', 'current_ctc',
                'marital_status', 'qualification_degree', 'specialization',
            ]);
        });
    }
};
