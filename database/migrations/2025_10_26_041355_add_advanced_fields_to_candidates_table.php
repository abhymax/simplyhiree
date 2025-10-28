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
        Schema::table('candidates', function (Blueprint $table) {
            
            // --- THIS IS THE FIX ---
            // We will add all columns without the ->after() command,
            // which avoids any errors about missing columns.
            
            if (!Schema::hasColumn('candidates', 'partner_id')) {
                // We make it nullable because existing rows won't have this value.
                $table->foreignId('partner_id')->nullable()->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('candidates', 'first_name')) {
                $table->string('first_name')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'last_name')) {
                $table->string('last_name')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'email')) {
                $table->string('email')->nullable()->unique();
            }
            if (!Schema::hasColumn('candidates', 'phone_number')) {
                $table->string('phone_number')->nullable()->unique();
            }
            if (!Schema::hasColumn('candidates', 'alternate_phone_number')) {
                $table->string('alternate_phone_number')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'location')) {
                $table->string('location')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'gender')) {
                $table->string('gender')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'job_interest')) {
                $table->string('job_interest')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'education_level')) {
                $table->string('education_level')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'experience_status')) {
                $table->string('experience_status')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'expected_ctc')) {
                $table->decimal('expected_ctc', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('candidates', 'notice_period')) {
                $table->string('notice_period')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'job_role_preference')) {
                $table->text('job_role_preference')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'languages_spoken')) {
                $table->text('languages_spoken')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'skills')) {
                $table->text('skills')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'resume_path')) {
                $table->string('resume_path')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn([
                'partner_id', 'first_name', 'last_name', 'email', 'phone_number', 'alternate_phone_number',
                'location', 'date_of_birth', 'gender', 'job_interest', 'education_level',
                'experience_status', 'expected_ctc', 'notice_period', 'job_role_preference',
                'languages_spoken', 'skills', 'resume_path'
            ]);
        });
    }
};