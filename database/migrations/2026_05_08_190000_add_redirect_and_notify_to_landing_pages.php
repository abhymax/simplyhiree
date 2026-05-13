<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->string('redirect_url', 500)->nullable()->after('contact_info');
            $table->string('notify_emails', 500)->nullable()->after('redirect_url');
        });
    }

    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropColumn(['redirect_url', 'notify_emails']);
        });
    }
};
