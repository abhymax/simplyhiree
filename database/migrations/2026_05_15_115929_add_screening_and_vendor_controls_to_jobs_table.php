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
            $table->boolean('screening_required')->default(true)->after('status');
            $table->integer('auto_forward_hours')->nullable()->after('screening_required');
            $table->integer('max_resume_per_vendor')->nullable()->after('auto_forward_hours');
            $table->timestamp('resume_submission_deadline')->nullable()->after('max_resume_per_vendor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn([
                'screening_required',
                'auto_forward_hours',
                'max_resume_per_vendor',
                'resume_submission_deadline'
            ]);
        });
    }
};
