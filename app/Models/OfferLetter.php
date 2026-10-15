<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class OfferLetter extends Model
{
    protected $fillable = [
        'serial_no',
        'job_application_id','candidate_id','candidate_user_id','template_id',
        'candidate_name','candidate_email','job_title','company_name',
        'signatory_name','signatory_designation','signatory_signature_path',
        'subject','heading','body_html','pdf_path','status','sent_at','created_by',
    ];
    protected $casts = ['sent_at' => 'datetime'];
    /**
     * Reserve the next serial number.
     *
     * Taken under a row lock inside a transaction, so two admins saving at the
     * same moment cannot be handed the same one. The old code read max(id) at
     * compose time and only wrote the result into the letter's HTML, which is
     * how SHPL/HR/OL/2026/3008 ended up on four letters.
     *
     * The sequence runs per calendar year and continues from the highest number
     * already issued for that year.
     */
    public static function reserveSerial(?string $year = null): string
    {
        $year = $year ?: date('Y');

        return \Illuminate\Support\Facades\DB::transaction(function () use ($year) {
            $highest = 3000;

            $issued = static::query()
                ->whereNotNull('serial_no')
                ->where('serial_no', 'like', '%/' . $year . '/%')
                ->lockForUpdate()
                ->pluck('serial_no');

            foreach ($issued as $serial) {
                if (preg_match('~/' . preg_quote($year, '~') . '/(\d+)$~', (string) $serial, $m)) {
                    $highest = max($highest, (int) $m[1]);
                }
            }

            return 'SHPL/HR/OL/' . $year . '/' . str_pad((string) ($highest + 1), 4, '0', STR_PAD_LEFT);
        });
    }

    public function application(): BelongsTo { return $this->belongsTo(JobApplication::class, 'job_application_id'); }
    public function template(): BelongsTo { return $this->belongsTo(OfferLetterTemplate::class, 'template_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
