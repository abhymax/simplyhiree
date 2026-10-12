<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How many chats a SimplyHiree team member will handle at once. Anything
 * beyond this stays in the waiting queue rather than being claimed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'chat_capacity')) {
                $table->unsignedTinyInteger('chat_capacity')->default(5)->after('team_modules');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'chat_capacity')) {
                $table->dropColumn('chat_capacity');
            }
        });
    }
};
