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
        Schema::create('partner_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('monthly_submission_limit')->nullable();
            $table->integer('max_team_members')->default(1);
            $table->boolean('can_view_premium_jobs')->default(false);
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
        });

        DB::table('partner_plans')->insert([
            ['name' => 'Free', 'monthly_submission_limit' => 10, 'max_team_members' => 1, 'can_view_premium_jobs' => false, 'price' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Basic', 'monthly_submission_limit' => 50, 'max_team_members' => 3, 'can_view_premium_jobs' => false, 'price' => 499, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pro', 'monthly_submission_limit' => null, 'max_team_members' => 10, 'can_view_premium_jobs' => true, 'price' => 999, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Enterprise', 'monthly_submission_limit' => null, 'max_team_members' => 50, 'can_view_premium_jobs' => true, 'price' => 2999, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_plans');
    }
};
