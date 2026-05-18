<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanChangeRequest extends Model
{
    protected $fillable = [
        'partner_id',
        'current_plan',
        'requested_plan',
        'status',
        'notes',
        'admin_notes',
        'actioned_at',
        'actioned_by_user_id',
    ];

    protected $casts = [
        'actioned_at' => 'datetime',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function actionedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actioned_by_user_id');
    }
}
