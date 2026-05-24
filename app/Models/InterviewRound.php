<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewRound extends Model
{
    use HasFactory;

    public const MAX_ROUNDS = 5;
    public const MODES = ['Online', 'In-person', 'Phone'];

    protected $fillable = [
        'job_application_id',
        'round_number',
        'scheduled_at',
        'mode',
        'meeting_link',
        'location',
        'interviewer_name',
        'status',
        'feedback',
        'rating',
        'recommendation',
        'feedback_submitted_at',
    ];

    protected $casts = [
        'scheduled_at'          => 'datetime',
        'feedback_submitted_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }
}
