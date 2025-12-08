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
        Schema::table('job_applications', function (Blueprint $table) {
            // This column will store: 'Joined', 'Did Not Join', 'Left'
            $table->string('joined_status')->nullable()->after('joining_date');
            
            // This will store the date the candidate left
            $table->timestamp('left_at')->nullable()->after('joined_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn('joined_status');
            $table->dropColumn('left_at');
        });
    }
};