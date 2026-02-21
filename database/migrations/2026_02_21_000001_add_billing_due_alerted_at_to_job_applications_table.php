<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->timestamp('billing_due_alerted_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn('billing_due_alerted_at');
        });
    }
};
