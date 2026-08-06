<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('jobs', 'work_mode')) {
                $table->string('work_mode', 50)->nullable()->after('job_type');
            }
            if (!Schema::hasColumn('jobs', 'shift')) {
                $table->string('shift', 50)->nullable()->after('work_mode');
            }
            if (!Schema::hasColumn('jobs', 'specialization')) {
                $table->string('specialization', 255)->nullable()->after('shift');
            }
            if (!Schema::hasColumn('jobs', 'notice_period')) {
                $table->string('notice_period', 100)->nullable()->after('specialization');
            }
            if (!Schema::hasColumn('jobs', 'languages')) {
                $table->string('languages', 255)->nullable()->after('notice_period');
            }
            if (!Schema::hasColumn('jobs', 'industry')) {
                $table->string('industry', 255)->nullable()->after('languages');
            }
            if (!Schema::hasColumn('jobs', 'department')) {
                $table->string('department', 255)->nullable()->after('industry');
            }
            if (!Schema::hasColumn('jobs', 'reporting_manager')) {
                $table->string('reporting_manager', 255)->nullable()->after('department');
            }
            if (!Schema::hasColumn('jobs', 'travel_required')) {
                $table->boolean('travel_required')->nullable()->after('reporting_manager');
            }
            if (!Schema::hasColumn('jobs', 'benefits')) {
                $table->text('benefits')->nullable()->after('travel_required');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn([
                'work_mode',
                'shift',
                'specialization',
                'notice_period',
                'languages',
                'industry',
                'department',
                'reporting_manager',
                'travel_required',
                'benefits',
            ]);
        });
    }
};
