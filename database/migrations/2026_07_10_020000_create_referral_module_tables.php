<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referral_partner_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('referral_code', 32)->unique();
            $table->string('partner_type')->default('Individual');
            $table->string('pan_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc')->nullable();
            $table->string('status')->default('pending');
            $table->string('agreement_version')->nullable();
            $table->timestamp('agreement_accepted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('referral_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_partner_id')->constrained('users')->cascadeOnDelete();
            $table->string('company_name');
            $table->string('contact_name');
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('service_required')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('submitted');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index(['email', 'phone_number']);
        });

        Schema::create('client_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('referral_partner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('referral_lead_id')->nullable()->constrained('referral_leads')->nullOnDelete();
            $table->string('referral_code_snapshot', 32);
            $table->string('status')->default('active');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['referral_partner_id', 'status']);
        });

        Schema::create('client_referral_commission_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_referral_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('commission_type');
            $table->decimal('commission_value', 12, 2);
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('configured_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('referral_commission_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_partner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('client_referral_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_application_id')->nullable()->constrained()->nullOnDelete();
            $table->string('entry_type')->default('commission');
            $table->string('status')->default('available');
            $table->decimal('revenue_amount', 12, 2)->default(0);
            $table->decimal('commission_amount', 12, 2);
            $table->string('commission_type_snapshot');
            $table->decimal('commission_value_snapshot', 12, 2);
            $table->timestamp('earned_at');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['client_referral_id', 'job_application_id', 'entry_type'], 'referral_ledger_source_unique');
            $table->index(['referral_partner_id', 'status', 'earned_at'], 'referral_ledger_partner_status_idx');
        });

        Schema::create('referral_withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_partner_id')->constrained('users')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('requested');
            $table->string('payment_reference')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['referral_partner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_withdrawal_requests');
        Schema::dropIfExists('referral_commission_ledgers');
        Schema::dropIfExists('client_referral_commission_rules');
        Schema::dropIfExists('client_referrals');
        Schema::dropIfExists('referral_leads');
        Schema::dropIfExists('referral_partner_profiles');
    }
};
