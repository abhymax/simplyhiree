<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerCreditNote extends Model
{
    protected $fillable = [
        'partner_id',
        'source_application_id',
        'amount',
        'status',
        'reason',
        'applied_at',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'applied_at' => 'datetime',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function sourceApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'source_application_id');
    }
}
