<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('interview_rounds')) {
            Schema::create('interview_rounds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('job_application_id')->constrained('job_applications')->cascadeOnDelete();
                $table->unsignedTinyInteger('round_number'); // 1-5
                $table->dateTime('scheduled_at');
                $table->string('mode', 20)->default('Online'); // Online / In-person / Phone
                $table->string('meeting_link')->nullable();
                $table->string('location')->nullable();
                $table->string('interviewer_name')->nullable();
                // Scheduled, Appeared, No-Show, Cancelled
                $table->string('status', 20)->default('Scheduled');
                $table->text('feedback')->nullable();
                $table->unsignedTinyInteger('rating')->nullable(); // 1-5
                // Pass to Next Round, Select Candidate, Reject
                $table->string('recommendation', 30)->nullable();
                $table->timestamp('feedback_submitted_at')->nullable();
                $table->timestamps();

                $table->index(['job_application_id', 'round_number']);
            });
        }

        // Backfill: convert legacy single-interview rows into a Round 1 entry
        $legacy = DB::table('job_applications')
            ->whereNotNull('interview_at')
            ->select(
                'id', 'interview_at', 'meeting_link', 'meeting_provider',
                'interview_location', 'interview_feedback', 'interview_rating',
                'interview_recommendation', 'interview_feedback_at', 'hiring_status'
            )
            ->get();

        foreach ($legacy as $row) {
            $exists = DB::table('interview_rounds')
                ->where('job_application_id', $row->id)
                ->where('round_number', 1)
                ->exists();
            if ($exists) continue;

            $status = 'Scheduled';
            if (in_array($row->hiring_status, ['Interviewed', 'Selected', 'Client Rejected'])) {
                $status = 'Appeared';
            } elseif ($row->hiring_status === 'No-Show') {
                $status = 'No-Show';
            }

            $mode = 'Online';
            if (!empty($row->interview_location)) $mode = 'In-person';
            elseif (!empty($row->meeting_link)) $mode = 'Online';

            DB::table('interview_rounds')->insert([
                'job_application_id'   => $row->id,
                'round_number'         => 1,
                'scheduled_at'         => $row->interview_at,
                'mode'                 => $mode,
                'meeting_link'         => $row->meeting_link,
                'location'             => $row->interview_location,
                'interviewer_name'     => null,
                'status'               => $status,
                'feedback'             => $row->interview_feedback,
                'rating'               => $row->interview_rating,
                'recommendation'       => $row->interview_recommendation,
                'feedback_submitted_at'=> $row->interview_feedback_at,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_rounds');
    }
};
