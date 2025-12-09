<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update Jobs Table
        Schema::table('jobs', function (Blueprint $table) {
            // Allow null user_id for "Simplyhiree" (Admin) posts
            $table->foreignId('user_id')->nullable()->change(); 
            
            // Visibility Setting: 'all' (default), 'selected'
            $table->string('partner_visibility')->default('all')->after('status');
        });

        // 2. Table for "Selected Partners" (Inclusion List)
        Schema::create('job_partner_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('partner_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // 3. Table for "Restricted Candidates" (Exclusion List)
        Schema::create('job_candidate_exclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_candidate_exclusions');
        Schema::dropIfExists('job_partner_access');
        Schema::table('jobs', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->dropColumn('partner_visibility');
        });
    }
};