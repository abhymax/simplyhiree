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
        // 1. Create lookup tables only if they don't exist
        if (!Schema::hasTable('experience_levels')) {
            Schema::create('experience_levels', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('education_levels')) {
            Schema::create('education_levels', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        // 2. Add columns to jobs table (Check if they exist first)
        Schema::table('jobs', function (Blueprint $table) {
            
            if (!Schema::hasColumn('jobs', 'openings')) {
                $table->integer('openings')->nullable()->after('salary');
            }
            if (!Schema::hasColumn('jobs', 'min_age')) {
                $table->integer('min_age')->nullable()->after('openings');
            }
            if (!Schema::hasColumn('jobs', 'max_age')) {
                $table->integer('max_age')->nullable()->after('min_age');
            }
            if (!Schema::hasColumn('jobs', 'gender_preference')) {
                $table->string('gender_preference')->nullable()->after('max_age');
            }
            if (!Schema::hasColumn('jobs', 'category')) {
                $table->string('category')->nullable()->after('gender_preference');
            }
            if (!Schema::hasColumn('jobs', 'job_type_tags')) {
                $table->json('job_type_tags')->nullable()->after('category');
            }
            if (!Schema::hasColumn('jobs', 'is_walkin')) {
                $table->boolean('is_walkin')->default(false)->after('job_type_tags');
            }
            if (!Schema::hasColumn('jobs', 'interview_slot')) {
                $table->dateTime('interview_slot')->nullable()->after('is_walkin');
            }

            // Foreign Keys
            if (!Schema::hasColumn('jobs', 'experience_level_id')) {
                $table->foreignId('experience_level_id')->nullable()->constrained('experience_levels')->after('experience_required');
            }
            if (!Schema::hasColumn('jobs', 'education_level_id')) {
                $table->foreignId('education_level_id')->nullable()->constrained('education_levels')->after('education_level');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            // Drop Foreign Keys if they exist
            if (Schema::hasColumn('jobs', 'experience_level_id')) {
                $table->dropForeign(['experience_level_id']);
                $table->dropColumn('experience_level_id');
            }
            if (Schema::hasColumn('jobs', 'education_level_id')) {
                $table->dropForeign(['education_level_id']);
                $table->dropColumn('education_level_id');
            }

            // Drop Columns
            $columnsToDrop = [
                'openings',
                'min_age',
                'max_age',
                'gender_preference',
                'category',
                'job_type_tags',
                'is_walkin',
                'interview_slot'
            ];
            
            $existingColumns = [];
            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('jobs', $col)) {
                    $existingColumns[] = $col;
                }
            }
            
            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });

        // Only drop tables if they exist
        if (Schema::hasTable('experience_levels')) {
            Schema::dropIfExists('experience_levels');
        }
        if (Schema::hasTable('education_levels')) {
            Schema::dropIfExists('education_levels');
        }
    }
};