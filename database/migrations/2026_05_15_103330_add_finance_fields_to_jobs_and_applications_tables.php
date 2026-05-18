<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('jobs', 'fee_type')) {
                $table->string('fee_type')->nullable()->after('payout_amount')->comment('flat or percentage');
                $table->decimal('fee_amount', 10, 2)->nullable()->after('fee_type');
                $table->integer('invoice_release_days')->default(0)->after('fee_amount');
                $table->integer('replacement_period_days')->nullable()->after('minimum_stay_days');
            }
        });

        Schema::table('job_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('job_applications', 'final_ctc')) {
                $table->decimal('final_ctc', 12, 2)->nullable()->after('hiring_status');
                $table->decimal('invoice_amount', 12, 2)->nullable()->after('final_ctc');
                $table->timestamp('invoice_generated_at')->nullable()->after('invoice_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['fee_type', 'fee_amount', 'invoice_release_days', 'replacement_period_days']);
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['final_ctc', 'invoice_amount', 'invoice_generated_at']);
        });
    }
};
