<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ClientReferralCommissionRule extends Model
{
    protected $fillable = ['client_referral_id','commission_type','commission_value','effective_from','effective_until','is_active','configured_by'];
    protected $casts = ['commission_value'=>'decimal:2','effective_from'=>'date','effective_until'=>'date','is_active'=>'boolean'];
    public function clientReferral() { return $this->belongsTo(ClientReferral::class); }
}
