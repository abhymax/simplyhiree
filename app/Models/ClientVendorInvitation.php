<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientVendorInvitation extends Model
{
    protected $fillable = [
        'client_id', 'name', 'email', 'phone', 'company',
        'status', 'invite_token', 'joined_partner_id', 'joined_at',
    ];

    protected $casts = ['joined_at' => 'datetime'];

    public function client(): BelongsTo       { return $this->belongsTo(User::class, 'client_id'); }
    public function joinedPartner(): BelongsTo { return $this->belongsTo(User::class, 'joined_partner_id'); }
}
