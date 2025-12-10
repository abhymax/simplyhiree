<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'title',
        'category_id',
        'location',
        'salary',
        'job_type',
        'description',
        'experience_level_id',
        'education_level_id',
        'application_deadline',
        'status',
        // Admin / Billing fields
        'payout_amount',
        'minimum_stay_days',
        'partner_visibility',
        // Advanced fields
        'skills_required',
        'company_website',
        'openings',
        'min_age',
        'max_age',
        'gender_preference',
    ];

    protected $casts = [
        'application_deadline' => 'date',
    ];

    /**
     * Get the user (Client) that owns the job.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the job category.
     */
    public function jobCategory(): BelongsTo // Renamed to avoid conflict with potential string column
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }

    /**
     * Get the experience level.
     */
    public function experienceLevel(): BelongsTo
    {
        return $this->belongsTo(ExperienceLevel::class);
    }

    /**
     * Get the education level.
     */
    public function educationLevel(): BelongsTo
    {
        return $this->belongsTo(EducationLevel::class);
    }

    // --- MISSING RELATIONSHIP FIXED HERE ---
    /**
     * Get the applications for the job.
     */
    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Partners specifically ALLOWED to see this job (when visibility is 'selected').
     */
    public function allowedPartners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'job_partner_visibility', 'job_id', 'partner_id');
    }

    /**
     * Partners specifically EXCLUDED from seeing this job.
     */
    public function excludedPartners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'job_partner_exclusions', 'job_id', 'partner_id');
    }

    /**
     * Candidates restricted from applying to this job.
     */
    public function restrictedCandidates(): BelongsToMany
    {
        return $this->belongsToMany(Candidate::class, 'job_candidate_restrictions', 'job_id', 'candidate_id');
    }
}