<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('resume_fingerprint', 64)->nullable()->after('resume_path')->index();
            $table->string('duplicate_status', 24)->default('clear')->after('resume_fingerprint')->index();
            $table->foreignId('duplicate_of_candidate_id')->nullable()->after('duplicate_status')->constrained('candidates')->nullOnDelete();
            $table->json('duplicate_reasons')->nullable()->after('duplicate_of_candidate_id');
            $table->foreignId('duplicate_reviewed_by')->nullable()->after('duplicate_reasons')->constrained('users')->nullOnDelete();
            $table->timestamp('duplicate_reviewed_at')->nullable()->after('duplicate_reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['duplicate_of_candidate_id']);
            $table->dropForeign(['duplicate_reviewed_by']);
            $table->dropIndex(['resume_fingerprint']);
            $table->dropIndex(['duplicate_status']);
            $table->dropColumn(['resume_fingerprint', 'duplicate_status', 'duplicate_of_candidate_id', 'duplicate_reasons', 'duplicate_reviewed_by', 'duplicate_reviewed_at']);
        });
    }
};
