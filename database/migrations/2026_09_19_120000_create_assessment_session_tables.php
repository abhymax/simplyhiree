<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One journey per candidate per job: holds the magic-link token,
        // email-OTP verification state, and which stage they are on.
        Schema::create('assessment_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->foreignId('job_application_id')->nullable()->constrained('job_applications')->nullOnDelete();
            $table->foreignId('candidate_id')->nullable()->constrained('candidates')->nullOnDelete();
            // Attribution: the partner/vendor account (owner) who lined up the candidate.
            $table->foreignId('partner_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('token', 64)->unique();
            $table->string('email');                       // snapshot of the address we verify
            $table->string('candidate_name')->nullable();

            // Email OTP
            $table->string('otp_hash')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->unsignedTinyInteger('otp_attempts')->default(0);
            $table->timestamp('otp_last_sent_at')->nullable();
            $table->timestamp('verified_at')->nullable();  // email ownership confirmed

            $table->unsignedTinyInteger('current_stage')->default(1);
            // pending | verified | in_progress | passed | failed | expired
            $table->string('status', 20)->default('pending');
            $table->timestamp('expires_at')->nullable();   // magic-link expiry
            $table->timestamps();

            $table->index(['job_id', 'candidate_id']);
            $table->index('status');
        });

        // One row per attempt at a single stage (assessment).
        Schema::create('assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_session_id')->constrained('assessment_sessions')->cascadeOnDelete();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->unsignedTinyInteger('stage_order')->default(1);
            $table->unsignedTinyInteger('attempt_number')->default(1);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();   // started_at + time limit
            $table->timestamp('submitted_at')->nullable();

            $table->unsignedSmallInteger('score')->default(0);
            $table->unsignedSmallInteger('total_marks')->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->boolean('passed')->default(false);
            // in_progress | submitted | expired
            $table->string('status', 20)->default('in_progress');

            // Basic anti-cheat counters.
            $table->unsignedSmallInteger('focus_lost_count')->default(0);

            // Frozen question order for this attempt (ids), so a resume shows the
            // same questions in the same order even with shuffle on.
            $table->json('question_order')->nullable();
            $table->timestamps();

            $table->index(['assessment_session_id', 'stage_order']);
        });

        // One row per answered question within an attempt.
        Schema::create('assessment_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_attempt_id')->constrained('assessment_attempts')->cascadeOnDelete();
            $table->foreignId('assessment_question_id')->constrained('assessment_questions')->cascadeOnDelete();
            $table->foreignId('assessment_question_option_id')->nullable()
                ->constrained('assessment_question_options')->nullOnDelete();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();

            $table->unique(['assessment_attempt_id', 'assessment_question_id'], 'aaa_attempt_question_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_attempt_answers');
        Schema::dropIfExists('assessment_attempts');
        Schema::dropIfExists('assessment_sessions');
    }
};
