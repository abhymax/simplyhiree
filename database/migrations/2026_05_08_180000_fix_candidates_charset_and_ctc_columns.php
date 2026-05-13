<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Convert candidate and user_profile tables to utf8mb4 so they can
        //    accept the rupee symbol, accents, emoji, etc. The string columns
        //    we added earlier inherited the old latin1 charset.
        DB::statement('ALTER TABLE `candidates` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        DB::statement('ALTER TABLE `user_profiles` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // 2. The form now treats CTC as a free-text field ("₹4.5 LPA", "NA", etc.)
        //    so the legacy decimal columns must become varchars.
        if (Schema::hasColumn('candidates', 'expected_ctc')) {
            DB::statement('ALTER TABLE `candidates` MODIFY `expected_ctc` VARCHAR(100) NULL');
        }
        if (Schema::hasColumn('user_profiles', 'expected_ctc')) {
            DB::statement('ALTER TABLE `user_profiles` MODIFY `expected_ctc` VARCHAR(100) NULL');
        }
    }

    public function down(): void
    {
        // Charset conversion is intentionally not reversed (data would be lost
        // for any rows that stored non-latin1 characters in the meantime).
        if (Schema::hasColumn('candidates', 'expected_ctc')) {
            DB::statement('ALTER TABLE `candidates` MODIFY `expected_ctc` DECIMAL(10,2) NULL');
        }
        if (Schema::hasColumn('user_profiles', 'expected_ctc')) {
            DB::statement('ALTER TABLE `user_profiles` MODIFY `expected_ctc` DECIMAL(10,2) NULL');
        }
    }
};
