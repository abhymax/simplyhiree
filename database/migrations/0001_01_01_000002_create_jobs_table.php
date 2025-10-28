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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('job_categories');
            
            // --- Standard Fields ---
            $table->string('title');
            $table->string('location');
            $table->string('company_name');
            $table->string('salary')->nullable();
            $table->enum('job_type', ['Full-time', 'Part-time', 'Contract', 'Internship'])->default('Full-time');
            $table->text('description');

            // --- Advanced Fields ---
            $table->string('experience_required')->default('Not specified');
            $table->string('education_level')->default('Not specified');
            $table->text('skills_required');
            $table->date('application_deadline');
            $table->string('company_website')->nullable();
            
            $table->string('status')->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};

