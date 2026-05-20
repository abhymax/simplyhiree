<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // jobs.payout_amount was DECIMAL(8,2) — max ₹999,999.99.
        // Clients post payouts well above this (₹10L+ for senior roles)
        // which caused "Out of range value for column 'payout_amount'".
        // Widen to DECIMAL(12,2) (max ~₹99.99 crore) which is sane for any
        // real-world commission.
        Schema::table('jobs', function (Blueprint $table) {
            $table->decimal('payout_amount', 12, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->decimal('payout_amount', 8, 2)->nullable()->change();
        });
    }
};
