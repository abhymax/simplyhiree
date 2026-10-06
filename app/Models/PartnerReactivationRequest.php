<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerReactivationRequest extends Model
{
    protected $fillable = [
        'user_id', 'message', 'status', 'requested_at',
        'reviewed_at', 'reviewed_by', 'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
