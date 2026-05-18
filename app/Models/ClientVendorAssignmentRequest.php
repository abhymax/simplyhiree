<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientVendorAssignmentRequest extends Model
{
    protected $fillable = [
        'client_id', 'vendor_count', 'industry_hint', 'location_hint',
        'notes', 'status', 'admin_notes', 'fulfilled_at', 'fulfilled_by_user_id',
    ];

    protected $casts = ['fulfilled_at' => 'datetime'];

    public function client(): BelongsTo      { return $this->belongsTo(User::class, 'client_id'); }
    public function fulfilledBy(): BelongsTo { return $this->belongsTo(User::class, 'fulfilled_by_user_id'); }
}
