<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            // null = no assessment required for this job.
            // pending | qualified | not_qualified
            $table->string('assessment_status', 20)->nullable()->after('hiring_status');
            $table->timestamp('assessment_qualified_at')->nullable()->after('assessment_status');
            $table->index('assessment_status');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropIndex(['assessment_status']);
            $table->dropColumn(['assessment_status', 'assessment_qualified_at']);
        });
    }
};
