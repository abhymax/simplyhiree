<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('job_applications', 'replacement_status')) {
                $table->string('replacement_status', 30)
                    ->nullable()
                    ->after('replacement_window_days')
                    ->comment('window_open | replacement_given | credit_pending | closed');
            }
            if (!Schema::hasColumn('job_applications', 'replacement_deadline')) {
                $table->timestamp('replacement_deadline')->nullable()->after('replacement_status');
            }
            if (!Schema::hasColumn('job_applications', 'replacement_application_id')) {
                $table->unsignedBigInteger('replacement_application_id')->nullable()->after('replacement_deadline');
                $table->index('replacement_application_id');
            }
            if (!Schema::hasColumn('job_applications', 'replacement_window_days_default')) {
                // Days the partner gets to provide a replacement, locked at request time.
                $table->unsignedSmallInteger('partner_replacement_window_days')->nullable()->after('replacement_application_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            foreach (['replacement_status','replacement_deadline','replacement_application_id','partner_replacement_window_days'] as $col) {
                if (Schema::hasColumn('job_applications', $col)) {
                    if ($col === 'replacement_application_id') $table->dropIndex(['replacement_application_id']);
                    $table->dropColumn($col);
                }
            }
        });
    }
};
