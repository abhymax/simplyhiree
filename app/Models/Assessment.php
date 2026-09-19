<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    protected $fillable = [
        'user_id', 'is_global', 'name', 'tag', 'description',
        'passing_percentage', 'time_limit_minutes', 'max_attempts',
        'shuffle_questions', 'status',
    ];

    protected $casts = [
        'is_global'          => 'boolean',
        'shuffle_questions'  => 'boolean',
        'passing_percentage' => 'integer',
        'time_limit_minutes' => 'integer',
        'max_attempts'       => 'integer',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AssessmentQuestion::class)->orderBy('sort_order');
    }

    public function totalMarks(): int
    {
        return (int) $this->questions->sum('marks');
    }

    /** Owner + superadmin can see it; global ones are visible to all. */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->hasRole('Superadmin')) {
            return $query;
        }
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhere('is_global', true);
        });
    }
}
