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
            $table->boolean('is_company_confidential')->default(false)->after('company_name');
            $table->string('staffing_model')->default('Permanent')->after('status');
            
            // Contract Staffing Fields
            $table->string('contract_billing_cycle')->nullable()->after('staffing_model'); // Monthly, Bi-weekly
            $table->string('contract_margin_type')->nullable()->after('contract_billing_cycle'); // Percentage, Fixed
            $table->string('contract_payroll_managed_by')->nullable()->after('contract_margin_type'); // Client, SimplyHiree
            
            // RPO Model Fields
            $table->decimal('rpo_monthly_retainer', 10, 2)->nullable()->after('contract_payroll_managed_by');
            $table->decimal('rpo_per_position_fee', 10, 2)->nullable()->after('rpo_monthly_retainer');
            $table->decimal('rpo_dedicated_recruiter_cost', 10, 2)->nullable()->after('rpo_per_position_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn([
                'is_company_confidential',
                'staffing_model',
                'contract_billing_cycle',
                'contract_margin_type',
                'contract_payroll_managed_by',
                'rpo_monthly_retainer',
                'rpo_per_position_fee',
                'rpo_dedicated_recruiter_cost'
            ]);
        });
    }
};
