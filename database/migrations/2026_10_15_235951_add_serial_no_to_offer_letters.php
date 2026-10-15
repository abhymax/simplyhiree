<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

/**
 * Give every offer letter a stored serial number.
 *
 * The reference used to be worked out at compose time from max(id) + 3001 and
 * only lived inside the letter's HTML, so it was a guess rather than a
 * reservation: composing several letters before saving handed them the same
 * number, and SHPL/HR/OL/2026/3008 reached four letters.
 *
 * Backfill records what each letter already displays, exactly, including
 * historical duplicates. For a sent letter that number is printed on the PDF
 * the candidate holds, so changing it would make our record disagree with
 * theirs. Only letters showing no number at all are issued one, taken from
 * above the highest number in use so it cannot collide. Duplicates are left for
 * a human to decide about, since re-issuing a number a candidate already has is
 * not a call a migration should make.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('offer_letters', 'serial_no')) {
            Schema::table('offer_letters', function (Blueprint $table) {
                $table->string('serial_no', 48)->nullable()->after('id');
                $table->index('serial_no');
            });
        }

        $rows = DB::table('offer_letters')->orderBy('id')->get(['id', 'body_html', 'created_at']);

        // Highest number already in use, so new ones start clear of everything.
        $highest = [];
        $found   = [];
        foreach ($rows as $row) {
            $ref = $this->refInBody((string) $row->body_html);
            $found[$row->id] = $ref;
            if ($ref && preg_match('~/(\d{4})/(\d+)$~', $ref, $m)) {
                $highest[$m[1]] = max($highest[$m[1]] ?? 3000, (int) $m[2]);
            }
        }

        foreach ($rows as $row) {
            $year = $row->created_at ? date('Y', strtotime($row->created_at)) : date('Y');

            if ($found[$row->id]) {
                $serial = $found[$row->id];           // kept as printed
            } else {
                $highest[$year] = ($highest[$year] ?? 3000) + 1;
                $serial = 'SHPL/HR/OL/' . $year . '/' . str_pad((string) $highest[$year], 4, '0', STR_PAD_LEFT);
            }

            DB::table('offer_letters')->where('id', $row->id)->update(['serial_no' => $serial]);
        }
    }

    /** The reference the letter displays: tagged span first, then plain text. */
    private function refInBody(string $html): ?string
    {
        if (preg_match('/<span data-field="ref_no">(.*?)<\/span>/s', $html, $m)) {
            $value = trim(strip_tags($m[1]));
            if ($value !== '' && !str_starts_with($value, '____')) {
                return $value;
            }
        }

        if (preg_match('~(SHPL/[A-Z]+/[A-Z]+/\d{4}/\d+)~', $html, $m)) {
            return $m[1];
        }

        return null;
    }

    public function down(): void
    {
        if (Schema::hasColumn('offer_letters', 'serial_no')) {
            Schema::table('offer_letters', function (Blueprint $table) {
                $table->dropIndex(['serial_no']);
                $table->dropColumn('serial_no');
            });
        }
    }
};
