
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
        Schema::table('jobs', function (Blueprint $table) {
            $table->integer('openings')->nullable()->after('salary');
            $table->string('education_required')->nullable()->after('openings');
            $table->string('experience_required')->nullable()->after('education_required');
            $table->integer('min_age')->nullable()->after('experience_required');
            $table->integer('max_age')->nullable()->after('min_age');
            $table->string('gender_preference')->nullable()->after('max_age');
            $table->string('category')->nullable()->after('gender_preference');
            $table->json('job_type_tags')->nullable()->after('category'); // For tags like 'Walkin', 'New'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn([
                'openings',
                'education_required',
                'experience_required',
                'min_age',
                'max_age',
                'gender_preference',
                'category',
                'job_type_tags'
            ]);
        });
    }
};
