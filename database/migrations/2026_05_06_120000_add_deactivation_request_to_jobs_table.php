<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->timestamp('deactivation_requested_at')->nullable()->after('status');
            $table->text('deactivation_reason')->nullable()->after('deactivation_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['deactivation_requested_at', 'deactivation_reason']);
        });
    }
};
