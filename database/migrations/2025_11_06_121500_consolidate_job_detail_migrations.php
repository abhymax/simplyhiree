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
        // Create lookup tables first
        Schema::create('experience_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('education_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Now, add all new columns to the jobs table
        Schema::table('jobs', function (Blueprint $table) {
            $table->integer('openings')->nullable()->after('salary');
            $table->integer('min_age')->nullable()->after('openings');
            $table->integer('max_age')->nullable()->after('min_age');
            $table->string('gender_preference')->nullable()->after('max_age');
            $table->string('category')->nullable()->after('gender_preference');
            $table->json('job_type_tags')->nullable()->after('category');
            $table->boolean('is_walkin')->default(false)->after('job_type_tags');
            $table->dateTime('interview_slot')->nullable()->after('is_walkin');

            // Add foreign key columns
            $table->foreignId('experience_level_id')->nullable()->constrained('experience_levels')->after('experience_required');
            $table->foreignId('education_level_id')->nullable()->constrained('education_levels')->after('education_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropForeign(['experience_level_id']);
            $table->dropColumn('experience_level_id');
            $table->dropForeign(['education_level_id']);
            $table->dropColumn('education_level_id');
            $table->dropColumn([
                'openings',
                'min_age',
                'max_age',
                'gender_preference',
                'category',
                'job_type_tags',
                'is_walkin',
                'interview_slot'
            ]);
        });

        Schema::dropIfExists('experience_levels');
        Schema::dropIfExists('education_levels');
    }
};
