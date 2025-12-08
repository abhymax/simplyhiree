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
        Schema::table('job_applications', function (Blueprint $table) {
            // Only add the column if it does not exist
            if (!Schema::hasColumn('job_applications', 'candidate_id')) {
                $table->foreignId('candidate_id')
                      ->nullable()
                      ->constrained('candidates')
                      ->onDelete('set null')
                      ->after('partner_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            if (Schema::hasColumn('job_applications', 'candidate_id')) {
                // Drop the foreign key and the column
                $table->dropForeign(['candidate_id']);
                $table->dropColumn('candidate_id');
            }
        });
    }
};