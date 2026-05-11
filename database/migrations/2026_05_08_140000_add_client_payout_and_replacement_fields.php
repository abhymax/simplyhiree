<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->decimal('client_payout_amount', 12, 2)->nullable()->after('payout_amount');
            $table->unsignedSmallInteger('client_payout_days')->nullable()->after('client_payout_amount');
            $table->unsignedSmallInteger('replacement_guarantee_days')->nullable()->after('client_payout_days');
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->timestamp('replacement_requested_at')->nullable()->after('left_at');
            $table->text('replacement_reason')->nullable()->after('replacement_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['client_payout_amount', 'client_payout_days', 'replacement_guarantee_days']);
        });
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['replacement_requested_at', 'replacement_reason']);
        });
    }
};
