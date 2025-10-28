<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema; // Import the Schema facade
use App\Models\JobCategory; // Import the model

class JobCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Temporarily disable foreign key checks to allow truncation.
        Schema::disableForeignKeyConstraints();

        // Prevents creating duplicates if the seeder is run multiple times.
        JobCategory::truncate();

        // IMPORTANT: Re-enable foreign key checks.
        Schema::enableForeignKeyConstraints();

        $categories = [
            'Technology & IT',
            'Marketing & Sales',
            'Human Resources',
            'Finance & Accounting',
            'Healthcare',
            'Engineering',
            'Customer Service',
            'Design & Creative',
            'Operations',
            'Education',
            'Legal',
            'Other',
        ];

        foreach ($categories as $category) {
            JobCategory::create(['name' => $category]);
        }
    }
}

