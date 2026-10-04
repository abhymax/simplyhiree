<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            if (!Schema::hasColumn('candidates', 'duplicate_blocked_job_id')) {
                $table->unsignedBigInteger('duplicate_blocked_job_id')->nullable()->after('duplicate_reasons');
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            if (Schema::hasColumn('candidates', 'duplicate_blocked_job_id')) {
                $table->dropColumn('duplicate_blocked_job_id');
            }
        });
    }
};
