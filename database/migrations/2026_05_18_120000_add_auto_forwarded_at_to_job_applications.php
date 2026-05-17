<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('job_applications', 'auto_forwarded_at')) {
                $table->timestamp('auto_forwarded_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            if (Schema::hasColumn('job_applications', 'auto_forwarded_at')) {
                $table->dropColumn('auto_forwarded_at');
            }
        });
    }
};
