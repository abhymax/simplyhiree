<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // A questionnaire / test (self-contained: holds its own MCQ questions).
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // owning client (null = superadmin/global)
            $table->boolean('is_global')->default(false);   // superadmin library, reusable by all
            $table->string('name');
            $table->string('tag')->nullable();              // e.g. Psychometric, Mechanical, Electrical
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('passing_percentage')->default(50);
            $table->unsignedSmallInteger('time_limit_minutes')->nullable(); // null = no limit
            $table->unsignedTinyInteger('max_attempts')->default(1);
            $table->boolean('shuffle_questions')->default(true);
            $table->string('status')->default('active');    // active | archived
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index('tag');
        });

        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->text('question_text');
            $table->unsignedSmallInteger('marks')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index('assessment_id');
        });

        Schema::create('assessment_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_question_id')->constrained('assessment_questions')->cascadeOnDelete();
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index('assessment_question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_question_options');
        Schema::dropIfExists('assessment_questions');
        Schema::dropIfExists('assessments');
    }
};
