<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Comment out or delete the user factory call
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Keep the call to your job category seeder
        $this->call([
            JobCategorySeeder::class,
            RolesAndPermissionsSeeder::class,
            ExperienceLevelSeeder::class,
            EducationLevelSeeder::class,
        ]);
    }
}