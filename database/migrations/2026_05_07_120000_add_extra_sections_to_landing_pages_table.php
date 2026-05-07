<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->json('trust_badges')->nullable()->after('cta_text');
            $table->text('earnings_summary')->nullable()->after('benefits');
            $table->json('career_outcomes')->nullable()->after('earnings_summary');
            $table->json('bonuses')->nullable()->after('career_outcomes');
            $table->string('contact_info')->nullable()->after('footer_disclaimer');
            $table->string('tagline')->nullable()->after('contact_info');
        });
    }

    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropColumn(['trust_badges', 'earnings_summary', 'career_outcomes', 'bonuses', 'contact_info', 'tagline']);
        });
    }
};
