<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('archived_by_role', 30)->nullable()->after('archived_at');
            $table->unsignedBigInteger('archived_by_user_id')->nullable()->after('archived_by_role');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['archived_by_role', 'archived_by_user_id']);
        });
    }
};
