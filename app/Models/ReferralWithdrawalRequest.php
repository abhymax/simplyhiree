<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ReferralWithdrawalRequest extends Model
{
    protected $fillable = ['referral_partner_id','amount','status','payment_reference','admin_notes','reviewed_by','reviewed_at','paid_at'];
    protected $casts = ['amount'=>'decimal:2','reviewed_at'=>'datetime','paid_at'=>'datetime'];
    public function referralPartner() { return $this->belongsTo(User::class, 'referral_partner_id'); }
}
