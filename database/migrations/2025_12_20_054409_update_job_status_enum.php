<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // We use raw SQL to modify the ENUM column because Laravel's ->change() 
        // sometimes has issues with ENUMs if doctrine/dbal isn't configured perfectly.
        
        // This adds 'on_hold' to the allowed list of statuses.
        DB::statement("ALTER TABLE jobs MODIFY COLUMN status ENUM('pending_approval', 'approved', 'rejected', 'closed', 'on_hold') DEFAULT 'pending_approval'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the original list (removing 'on_hold')
        // Warning: This will fail if you have rows with 'on_hold' status when rolling back.
        DB::statement("ALTER TABLE jobs MODIFY COLUMN status ENUM('pending_approval', 'approved', 'rejected', 'closed') DEFAULT 'pending_approval'");
    }
};