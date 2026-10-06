<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ReferralPartnerProfile extends Model
{
    protected $fillable = ['user_id','referral_code','partner_type','pan_number','bank_account_name','bank_account_number','bank_ifsc','status','agreement_version','agreement_accepted_at','agreement_accepted_ip','agreement_accepted_user_agent','agreement_acceptance_method','approved_at','approved_by'];
    protected $casts = ['agreement_accepted_at'=>'datetime','approved_at'=>'datetime','pan_number'=>'encrypted','bank_account_name'=>'encrypted','bank_account_number'=>'encrypted','bank_ifsc'=>'encrypted'];
    public function user() { return $this->belongsTo(User::class); }
}
