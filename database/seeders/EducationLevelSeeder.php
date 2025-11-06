<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EducationLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            ['name' => 'High School'],
            ['name' => 'Diploma'],
            ['name' => 'Bachelors Degree'],
            ['name' => 'Masters Degree'],
            ['name' => 'PhD'],
        ];

        DB::table('education_levels')->insert($levels);
    }
}
