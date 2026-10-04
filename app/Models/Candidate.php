<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'preferred_locations',
        'date_of_birth',
        'gender',
        'marital_status',
        'job_interest',
        'education_level',
        'qualification_degree',
        'specialization',
        'experience_status',
        'total_experience_years',
        'total_experience_months',
        'current_company',
        'current_designation',
        'current_ctc',
        'expected_ctc',
        'notice_period',
        'job_role_preference',
        'languages_spoken',
        'skills',
        'resume_path',
        'resume_fingerprint',
        'duplicate_status',
        'duplicate_blocked_job_id',
        'duplicate_of_candidate_id',
        'duplicate_reasons',
        'duplicate_reviewed_by',
        'duplicate_reviewed_at',
    ];

    protected $casts = [
        'preferred_locations' => 'array',
        'date_of_birth'       => 'date',
        'duplicate_reasons'   => 'array',
        'duplicate_reviewed_at' => 'datetime',
    ];

    /**
     * Get the partner that owns this candidate profile.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'candidate_id');
    }

    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'duplicate_of_candidate_id');
    }

    protected function candidateCode(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->id ? sprintf('SH-CAN-%06d', (int) $this->id) : null,
        );
    }
}
