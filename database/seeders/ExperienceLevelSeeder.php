<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExperienceLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            ['name' => 'Fresher'],
            ['name' => '0-1 Years'],
            ['name' => '1-3 Years'],
            ['name' => '3-5 Years'],
            ['name' => '5+ Years'],
        ];

        DB::table('experience_levels')->insert($levels);
    }
}
