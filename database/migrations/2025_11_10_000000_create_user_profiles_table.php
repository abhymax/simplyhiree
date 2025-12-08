<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Contact & Personal
            $table->string('phone_number')->nullable();
            $table->string('location')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable(); // Male, Female, Other
            
            // Professional
            $table->string('experience_status')->nullable(); // Fresher, Experienced
            $table->string('current_role')->nullable();
            $table->decimal('expected_ctc', 10, 2)->nullable();
            $table->string('notice_period')->nullable();
            $table->text('skills')->nullable(); // Comma separated or JSON
            
            // Resume
            $table->string('resume_path')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};