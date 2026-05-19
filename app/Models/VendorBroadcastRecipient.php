<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBroadcastRecipient extends Model
{
    protected $fillable = [
        'broadcast_id', 'partner_id',
        'whatsapp_status', 'email_status',
        'error', 'delivered_at',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    public function broadcast(): BelongsTo
    {
        return $this->belongsTo(VendorBroadcast::class, 'broadcast_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}
