<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorBroadcast extends Model
{
    protected $fillable = [
        'sender_id', 'sender_role', 'subject', 'body',
        'template_key', 'channels',
        'recipient_count', 'sent_count', 'failed_count', 'dispatched_at',
    ];

    protected $casts = [
        'dispatched_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(VendorBroadcastRecipient::class, 'broadcast_id');
    }

    public function channelList(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $this->channels ?? ''))));
    }
}
