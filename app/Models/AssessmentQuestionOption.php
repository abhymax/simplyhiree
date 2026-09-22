<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentQuestionOption extends Model
{
    protected $fillable = [
        'assessment_question_id', 'option_text', 'is_correct', 'weight', 'sort_order',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'weight'     => 'integer',
        'sort_order' => 'integer',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestion::class, 'assessment_question_id');
    }
}
