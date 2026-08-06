<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration {
    /**
     * Replace the education_levels master list with the requested values.
     * Existing rows are renamed in place (not deleted) so that any job
     * already referencing education_level_id keeps a valid, non-orphaned
     * relation. A single new value ("12th Pass") is inserted.
     */
    public function up(): void
    {
        $now = now();

        $renames = [
            'High School'      => '10th Pass',
            'Bachelors Degree' => 'Graduate',
            'Masters Degree'   => 'Post Graduate',
            'PhD'              => 'Any Qualification',
            // 'Diploma' stays 'Diploma'
        ];

        foreach ($renames as $from => $to) {
            DB::table('education_levels')->where('name', $from)
                ->update(['name' => $to, 'updated_at' => $now]);
        }

        // Ensure every required value exists (idempotent).
        foreach (['10th Pass', '12th Pass', 'Diploma', 'Graduate', 'Post Graduate', 'Any Qualification'] as $name) {
            if (!DB::table('education_levels')->where('name', $name)->exists()) {
                DB::table('education_levels')->insert([
                    'name'       => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        Cache::forget('education_levels');
    }

    public function down(): void
    {
        $now = now();

        $reverts = [
            '10th Pass'         => 'High School',
            'Graduate'          => 'Bachelors Degree',
            'Post Graduate'     => 'Masters Degree',
            'Any Qualification' => 'PhD',
        ];

        foreach ($reverts as $from => $to) {
            DB::table('education_levels')->where('name', $from)
                ->update(['name' => $to, 'updated_at' => $now]);
        }

        DB::table('education_levels')->where('name', '12th Pass')->delete();

        Cache::forget('education_levels');
    }
};
