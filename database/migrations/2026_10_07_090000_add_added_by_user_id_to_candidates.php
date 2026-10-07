<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Candidates are stored under the partner-account OWNER's id, so there was
     * no record of which team member added one. Track the author so a team
     * member can be shown only their own submissions.
     */
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            if (!Schema::hasColumn('candidates', 'added_by_user_id')) {
                $table->unsignedBigInteger('added_by_user_id')->nullable()->after('partner_id');
                $table->index('added_by_user_id');
            }
        });

        // Backfill: if every application for a candidate was submitted by the
        // same user, that user added the candidate.
        $rows = DB::table('job_applications')
            ->whereNotNull('submitted_by_user_id')
            ->whereNotNull('candidate_id')
            ->select('candidate_id', DB::raw('COUNT(DISTINCT submitted_by_user_id) AS submitters'), DB::raw('MIN(submitted_by_user_id) AS submitter'))
            ->groupBy('candidate_id')
            ->having('submitters', '=', 1)
            ->get();

        foreach ($rows as $row) {
            DB::table('candidates')
                ->where('id', $row->candidate_id)
                ->whereNull('added_by_user_id')
                ->update(['added_by_user_id' => $row->submitter]);
        }
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            if (Schema::hasColumn('candidates', 'added_by_user_id')) {
                $table->dropIndex(['added_by_user_id']);
                $table->dropColumn('added_by_user_id');
            }
        });
    }
};
