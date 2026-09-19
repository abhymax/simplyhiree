<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentAttemptAnswer extends Model
{
    protected $fillable = [
        'assessment_attempt_id', 'assessment_question_id',
        'assessment_question_option_id', 'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(AssessmentAttempt::class, 'assessment_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestion::class, 'assessment_question_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestionOption::class, 'assessment_question_option_id');
    }
}
