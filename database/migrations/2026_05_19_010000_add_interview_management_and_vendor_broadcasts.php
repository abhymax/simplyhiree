<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Extend job_applications with interview/meeting fields
        Schema::table('job_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('job_applications', 'meeting_link')) {
                $table->string('meeting_link', 500)->nullable()->after('interview_at')
                    ->comment('Zoom / Google Meet / pasted URL');
            }
            if (!Schema::hasColumn('job_applications', 'meeting_provider')) {
                $table->string('meeting_provider', 20)->nullable()->after('meeting_link')
                    ->comment('zoom | meet | teams | inperson | other');
            }
            if (!Schema::hasColumn('job_applications', 'interview_location')) {
                $table->string('interview_location')->nullable()->after('meeting_provider')
                    ->comment('In-person location, if applicable');
            }
            if (!Schema::hasColumn('job_applications', 'interview_reminder_sent_at')) {
                $table->timestamp('interview_reminder_sent_at')->nullable()->after('interview_location');
                $table->index('interview_reminder_sent_at');
            }
            // Feedback fields
            if (!Schema::hasColumn('job_applications', 'interview_rating')) {
                $table->tinyInteger('interview_rating')->nullable()->after('interview_reminder_sent_at')
                    ->comment('1-5 stars given by interviewer');
            }
            if (!Schema::hasColumn('job_applications', 'interview_feedback')) {
                $table->text('interview_feedback')->nullable()->after('interview_rating');
            }
            if (!Schema::hasColumn('job_applications', 'interview_feedback_at')) {
                $table->timestamp('interview_feedback_at')->nullable()->after('interview_feedback');
            }
            if (!Schema::hasColumn('job_applications', 'interview_recommendation')) {
                $table->string('interview_recommendation', 30)->nullable()->after('interview_feedback_at')
                    ->comment('select | reject | second_round | on_hold');
            }
        });

        // 2. Vendor broadcasts (one-to-many: a broadcast goes to many partner recipients)
        Schema::create('vendor_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete()
                ->comment('Admin or client who initiated the broadcast');
            $table->string('sender_role', 20)->default('admin')
                ->comment('admin | client');
            $table->string('subject', 255);
            $table->text('body');
            $table->string('template_key', 60)->nullable()
                ->comment('urgent_hiring | salary_update | custom');
            $table->string('channels', 60)->default('whatsapp,email')
                ->comment('Comma-separated list: whatsapp, email');
            $table->unsignedInteger('recipient_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamps();
            $table->index(['sender_id', 'dispatched_at']);
        });

        Schema::create('vendor_broadcast_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_id')->constrained('vendor_broadcasts')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $table->string('whatsapp_status', 20)->nullable()
                ->comment('sent | failed | skipped');
            $table->string('email_status', 20)->nullable();
            $table->text('error')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->unique(['broadcast_id', 'partner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_broadcast_recipients');
        Schema::dropIfExists('vendor_broadcasts');

        Schema::table('job_applications', function (Blueprint $table) {
            foreach ([
                'meeting_link', 'meeting_provider', 'interview_location',
                'interview_reminder_sent_at', 'interview_rating',
                'interview_feedback', 'interview_feedback_at',
                'interview_recommendation',
            ] as $col) {
                if (Schema::hasColumn('job_applications', $col)) {
                    try { $table->dropIndex([$col]); } catch (\Throwable $e) {}
                    $table->dropColumn($col);
                }
            }
        });
    }
};
