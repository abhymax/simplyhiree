<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_plans', function (Blueprint $t) {
            $t->boolean('is_purchasable')->default(false)->after('price_suffix');
            $t->unsignedSmallInteger('duration_days')->default(30)->after('is_purchasable');
        });

        Schema::table('users', function (Blueprint $t) {
            $t->timestamp('plan_started_at')->nullable()->after('partner_plan');
            $t->timestamp('plan_expires_at')->nullable()->after('plan_started_at');
            $t->date('plan_expiry_reminded_on')->nullable()->after('plan_expires_at');
        });

        Schema::create('partner_payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $t->string('plan_name');
            $t->unsignedSmallInteger('duration_days')->default(30);
            $t->decimal('base_amount', 10, 2);      // plan price (₹)
            $t->decimal('gst_amount', 10, 2)->default(0);
            $t->decimal('total_amount', 10, 2);     // base + gst (₹)
            $t->string('currency', 8)->default('INR');
            $t->string('razorpay_order_id')->nullable()->index();
            $t->string('razorpay_payment_id')->nullable()->index();
            $t->string('razorpay_signature')->nullable();
            // created | paid | failed
            $t->string('status', 20)->default('created');
            $t->timestamp('paid_at')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();
            $t->index(['partner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_payments');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['plan_started_at', 'plan_expires_at', 'plan_expiry_reminded_on']));
        Schema::table('partner_plans', fn (Blueprint $t) => $t->dropColumn(['is_purchasable', 'duration_days']));
    }
};
