<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('job_applications', 'replacement_window_days')) {
                $table->unsignedSmallInteger('replacement_window_days')->nullable()->after('replacement_reason')
                    ->comment('Replacement guarantee window locked in at hire time from the resolved commercial slab/profile/flat row');
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            if (Schema::hasColumn('job_applications', 'replacement_window_days')) {
                $table->dropColumn('replacement_window_days');
            }
        });
    }
};
