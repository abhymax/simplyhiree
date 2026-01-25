<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('jobs', function (Blueprint $table) {
        $table->integer('min_experience')->nullable()->after('description');
        $table->integer('max_experience')->nullable()->after('min_experience');
        
        // We make the old column nullable so the app doesn't crash before you update existing data
        $table->unsignedBigInteger('experience_level_id')->nullable()->change();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            //
        });
    }
};
