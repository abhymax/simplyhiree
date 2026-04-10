<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            // Prevent a direct candidate from applying to the same job twice
            $table->unique(['job_id', 'candidate_user_id'], 'unique_job_candidate_user');
            // Prevent a partner from submitting the same candidate to the same job twice
            $table->unique(['job_id', 'candidate_id'], 'unique_job_candidate');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropUnique('unique_job_candidate_user');
            $table->dropUnique('unique_job_candidate');
        });
    }
};
