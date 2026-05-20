<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Both columns were declared NOT NULL but are marked optional in the
        // client + admin job-posting forms. Make them nullable so leaving
        // the inputs blank no longer fails the insert.
        Schema::table('jobs', function (Blueprint $table) {
            $table->text('skills_required')->nullable()->change();
            $table->date('application_deadline')->nullable()->change();
        });

        // Backfill any existing empty strings to NULL for consistency.
        try {
            DB::statement("UPDATE jobs SET skills_required = NULL WHERE skills_required = ''");
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->text('skills_required')->nullable(false)->change();
            $table->date('application_deadline')->nullable(false)->change();
        });
    }
};
