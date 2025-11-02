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

        // This will store the client's pipeline status:
        // e.g., 'Interview Scheduled', 'Interviewed', 'Selected', 'Joined', 'Left', 'Client Rejected'
        $table->string('hiring_status')->nullable()->after('status');

        // This stores the interview date and time
        $table->dateTime('interview_at')->nullable()->after('hiring_status');

        // This stores the candidate's joining date
        $table->date('joining_date')->nullable()->after('interview_at');

        // This stores client notes (e.g., interview feedback, rejection reason)
        $table->text('client_notes')->nullable()->after('joining_date');

    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
        $table->dropColumn(['hiring_status', 'interview_at', 'joining_date', 'client_notes']);
    });
    }
};
