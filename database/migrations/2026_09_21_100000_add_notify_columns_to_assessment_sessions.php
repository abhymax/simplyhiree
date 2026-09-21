<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_sessions', function (Blueprint $table) {
            $table->timestamp('last_reminded_at')->nullable()->after('expires_at');
            $table->unsignedTinyInteger('reminder_count')->default(0)->after('last_reminded_at');
            $table->timestamp('result_notified_at')->nullable()->after('reminder_count');
            // Highest stage_order the candidate has already been told is unlocked.
            $table->unsignedTinyInteger('unlock_notified_stage')->default(0)->after('result_notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_sessions', function (Blueprint $table) {
            $table->dropColumn(['last_reminded_at', 'reminder_count', 'result_notified_at', 'unlock_notified_stage']);
        });
    }
};
