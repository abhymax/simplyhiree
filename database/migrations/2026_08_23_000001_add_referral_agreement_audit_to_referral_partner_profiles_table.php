<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referral_partner_profiles', function (Blueprint $table) {
            $table->string('agreement_accepted_ip', 45)->nullable()->after('agreement_accepted_at');
            $table->text('agreement_accepted_user_agent')->nullable()->after('agreement_accepted_ip');
            $table->string('agreement_acceptance_method', 30)->nullable()->after('agreement_accepted_user_agent');
        });
    }

    public function down(): void
    {
        Schema::table('referral_partner_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'agreement_accepted_ip',
                'agreement_accepted_user_agent',
                'agreement_acceptance_method',
            ]);
        });
    }
};
