<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ClientReferral extends Model
{
    protected $fillable = ['client_id','referral_partner_id','referral_lead_id','referral_code_snapshot','status','approved_by','approved_at'];
    protected $casts = ['approved_at'=>'datetime'];
    public function client() { return $this->belongsTo(User::class, 'client_id'); }
    public function referralPartner() { return $this->belongsTo(User::class, 'referral_partner_id'); }
    public function rule() { return $this->hasOne(ClientReferralCommissionRule::class); }
    public function ledgerEntries() { return $this->hasMany(ReferralCommissionLedger::class); }
}
