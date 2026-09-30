<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interview_rounds', function (Blueprint $table) {
            if (!Schema::hasColumn('interview_rounds', 'cc_emails')) {
                $table->text('cc_emails')->nullable()->after('candidate_message');
            }
            if (!Schema::hasColumn('interview_rounds', 'cancel_reason')) {
                $table->text('cancel_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('interview_rounds', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancel_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('interview_rounds', function (Blueprint $table) {
            foreach (['cc_emails', 'cancel_reason', 'cancelled_at'] as $col) {
                if (Schema::hasColumn('interview_rounds', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
