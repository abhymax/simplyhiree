<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ReferralCommissionLedger extends Model
{
    protected $fillable = ['referral_partner_id','client_referral_id','job_application_id','entry_type','status','revenue_amount','commission_amount','commission_type_snapshot','commission_value_snapshot','earned_at','verified_by','notes'];
    protected $casts = ['revenue_amount'=>'decimal:2','commission_amount'=>'decimal:2','commission_value_snapshot'=>'decimal:2','earned_at'=>'datetime'];
    public function referralPartner() { return $this->belongsTo(User::class, 'referral_partner_id'); }
    public function clientReferral() { return $this->belongsTo(ClientReferral::class); }
    public function application() { return $this->belongsTo(JobApplication::class, 'job_application_id'); }
}
