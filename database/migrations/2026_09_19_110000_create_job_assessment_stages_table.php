<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_assessment_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->unsignedTinyInteger('stage_order')->default(1);
            // Hours the candidate has to START the next stage once this stage is
            // passed. NULL = no window (next stage stays open indefinitely).
            $table->unsignedSmallInteger('next_stage_start_hours')->nullable();
            $table->timestamps();

            $table->unique(['job_id', 'stage_order']);
            $table->index('job_id');
            $table->index('assessment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_assessment_stages');
    }
};
