<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerPayment extends Model
{
    protected $fillable = [
        'partner_id', 'plan_name', 'duration_days',
        'base_amount', 'gst_amount', 'total_amount', 'currency',
        'razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature',
        'status', 'paid_at', 'meta',
    ];

    protected $casts = [
        'base_amount'  => 'decimal:2',
        'gst_amount'   => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at'      => 'datetime',
        'meta'         => 'array',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}
