<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            // Only add the 'id' column if it does not already exist
            if (!Schema::hasColumn('candidates', 'id')) {
                $table->id()->first();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            // Only drop the 'id' column if it exists
            if (Schema::hasColumn('candidates', 'id')) {
                $table->dropColumn('id');
            }
        });
    }
};