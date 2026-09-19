<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentAttempt extends Model
{
    protected $fillable = [
        'assessment_session_id', 'assessment_id', 'stage_order', 'attempt_number',
        'started_at', 'expires_at', 'submitted_at',
        'score', 'total_marks', 'percentage', 'passed', 'status',
        'focus_lost_count', 'question_order',
    ];

    protected $casts = [
        'started_at'    => 'datetime',
        'expires_at'    => 'datetime',
        'submitted_at'  => 'datetime',
        'passed'        => 'boolean',
        'percentage'    => 'decimal:2',
        'question_order' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(AssessmentSession::class, 'assessment_session_id');
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AssessmentAttemptAnswer::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
