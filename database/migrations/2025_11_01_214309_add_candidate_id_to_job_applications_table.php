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
            // This column links to the 'candidates' table (the partner's pool)
            $table->foreignId('candidate_id')
                  ->nullable()
                  ->constrained('candidates')
                  ->onDelete('set null')
                  ->after('partner_id'); // Places it after the 'partner_id' column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            //
        });
    }
};
