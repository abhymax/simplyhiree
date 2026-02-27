<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Candidate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'partner_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'alternate_phone_number',
        'location',
        'date_of_birth',
        'gender',
        'job_interest',
        'education_level',
        'experience_status',
        'expected_ctc',
        'notice_period',
        'job_role_preference',
        'languages_spoken',
        'skills',
        'resume_path',
    ];

    /**
     * Get the partner that owns this candidate profile.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    protected function candidateCode(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->id ? sprintf('SH-CAN-%06d', (int) $this->id) : null,
        );
    }
}
