<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('partner_plans', 'subtitle')) {
                $table->string('subtitle')->nullable()->after('name')
                    ->comment('e.g. "Entry / Freshers", "High Performer", "Big Vendors"');
            }
            if (!Schema::hasColumn('partner_plans', 'price_max')) {
                $table->decimal('price_max', 10, 2)->nullable()->after('price')
                    ->comment('Upper bound for price ranges (e.g. ₹1,999-2,999)');
            }
            if (!Schema::hasColumn('partner_plans', 'price_suffix')) {
                $table->string('price_suffix', 40)->nullable()->after('price_max')
                    ->comment('e.g. "/month", "/month (custom)"');
            }
            if (!Schema::hasColumn('partner_plans', 'commission_min')) {
                $table->decimal('commission_min', 5, 2)->nullable()->after('price_suffix');
            }
            if (!Schema::hasColumn('partner_plans', 'commission_max')) {
                $table->decimal('commission_max', 5, 2)->nullable()->after('commission_min');
            }
            if (!Schema::hasColumn('partner_plans', 'features')) {
                $table->json('features')->nullable()->after('commission_max')
                    ->comment('Array of feature bullets (positive)');
            }
            if (!Schema::hasColumn('partner_plans', 'non_features')) {
                $table->json('non_features')->nullable()->after('features')
                    ->comment('Array of features NOT included (strikethrough)');
            }
            if (!Schema::hasColumn('partner_plans', 'is_most_popular')) {
                $table->boolean('is_most_popular')->default(false)->after('non_features');
            }
            if (!Schema::hasColumn('partner_plans', 'accent_color')) {
                $table->string('accent_color', 20)->default('slate')->after('is_most_popular')
                    ->comment('slate | blue | purple | rose | emerald');
            }
            if (!Schema::hasColumn('partner_plans', 'sort_order')) {
                $table->unsignedSmallInteger('sort_order')->default(0)->after('accent_color');
            }
        });

        // Seed sensible defaults for the existing 4 plans so the partner page
        // mirrors what was previously hardcoded.
        $defaults = [
            'Free' => [
                'subtitle' => 'Entry / Freshers',
                'price_suffix' => '/month',
                'commission_min' => 20, 'commission_max' => 30,
                'features' => json_encode([
                    '5-10 job submissions / month',
                    '20-30% commission per closure',
                    'Basic profile visibility',
                ]),
                'non_features' => json_encode([
                    'Bulk hiring projects',
                    'Priority support',
                ]),
                'is_most_popular' => 0, 'accent_color' => 'slate', 'sort_order' => 10,
            ],
            'Basic' => [
                'subtitle' => 'Starter Paid · Serious Freelancers',
                'price_suffix' => '/month',
                'commission_min' => 15, 'commission_max' => 20,
                'features' => json_encode([
                    '30-50 job submissions / month',
                    '15-20% commission per closure',
                    'WhatsApp support group',
                    'Medium profile visibility boost',
                    'Early access (2-4 hrs before Free)',
                ]),
                'non_features' => json_encode([]),
                'is_most_popular' => 0, 'accent_color' => 'blue', 'sort_order' => 20,
            ],
            'Pro' => [
                'subtitle' => 'High Performer · Experienced Recruiters',
                'price_max' => 2999,
                'price_suffix' => '/month',
                'commission_min' => 10, 'commission_max' => 15,
                'features' => json_encode([
                    'Unlimited job submissions',
                    '10-15% commission (lowest)',
                    'Dedicated Account Manager',
                    'Priority payouts',
                    'Bulk hiring projects access',
                    'Featured profile (top listing)',
                ]),
                'non_features' => json_encode([]),
                'is_most_popular' => 1, 'accent_color' => 'purple', 'sort_order' => 30,
            ],
            'Enterprise' => [
                'subtitle' => 'Big Vendors · Agencies',
                'price_max' => 15000,
                'price_suffix' => '/month (custom)',
                'commission_min' => 0, 'commission_max' => 0,
                'features' => json_encode([
                    'Dedicated hiring projects',
                    'Direct client connection',
                    'Unlimited team logins',
                    'Zero / very-low commission',
                    'SLA-based hiring contracts',
                    'Dashboard + reporting',
                ]),
                'non_features' => json_encode([]),
                'is_most_popular' => 0, 'accent_color' => 'rose', 'sort_order' => 40,
            ],
        ];

        foreach ($defaults as $name => $data) {
            DB::table('partner_plans')->where('name', $name)->update($data);
        }
    }

    public function down(): void
    {
        Schema::table('partner_plans', function (Blueprint $table) {
            foreach (['subtitle','price_max','price_suffix','commission_min','commission_max','features','non_features','is_most_popular','accent_color','sort_order'] as $col) {
                if (Schema::hasColumn('partner_plans', $col)) $table->dropColumn($col);
            }
        });
    }
};
