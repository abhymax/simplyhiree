<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class OfferLetter extends Model
{
    protected $fillable = [
        'job_application_id','candidate_id','candidate_user_id','template_id',
        'candidate_name','candidate_email','job_title','company_name',
        'signatory_name','signatory_designation','signatory_signature_path',
        'subject','heading','body_html','pdf_path','status','sent_at','created_by',
    ];
    protected $casts = ['sent_at' => 'datetime'];
    public function application(): BelongsTo { return $this->belongsTo(JobApplication::class, 'job_application_id'); }
    public function template(): BelongsTo { return $this->belongsTo(OfferLetterTemplate::class, 'template_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
