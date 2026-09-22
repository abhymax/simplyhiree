<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            // mcq = single correct option (marks). weighted = Likert/psychometric
            // where every option carries a point weight and questions roll up
            // into competency categories.
            $table->string('scoring_type', 20)->default('mcq')->after('tag');
        });

        Schema::table('assessment_questions', function (Blueprint $table) {
            // Competency / section a weighted question belongs to.
            $table->string('category')->nullable()->after('question_text');
        });

        Schema::table('assessment_question_options', function (Blueprint $table) {
            // Points earned when this option is chosen (weighted scoring).
            $table->smallInteger('weight')->default(0)->after('is_correct');
        });

        Schema::table('assessment_attempts', function (Blueprint $table) {
            // Snapshot of per-category totals for weighted attempts:
            // [{category, score, max, percentage}]
            $table->json('category_scores')->nullable()->after('question_order');
        });
    }

    public function down(): void
    {
        Schema::table('assessments', fn (Blueprint $t) => $t->dropColumn('scoring_type'));
        Schema::table('assessment_questions', fn (Blueprint $t) => $t->dropColumn('category'));
        Schema::table('assessment_question_options', fn (Blueprint $t) => $t->dropColumn('weight'));
        Schema::table('assessment_attempts', fn (Blueprint $t) => $t->dropColumn('category_scores'));
    }
};
