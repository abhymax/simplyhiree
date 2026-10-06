<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ReferralLead extends Model
{
    protected $fillable = ['referral_partner_id','company_name','contact_name','email','phone_number','service_required','notes','status','admin_notes','reviewed_by','reviewed_at'];
    protected $casts = ['reviewed_at'=>'datetime'];
    public function referralPartner() { return $this->belongsTo(User::class, 'referral_partner_id'); }
}
