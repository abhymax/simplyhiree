<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interview_rounds', function (Blueprint $table) {
            if (!Schema::hasColumn('interview_rounds', 'candidate_message')) {
                $table->text('candidate_message')->nullable()->after('interviewer_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('interview_rounds', function (Blueprint $table) {
            if (Schema::hasColumn('interview_rounds', 'candidate_message')) {
                $table->dropColumn('candidate_message');
            }
        });
    }
};
