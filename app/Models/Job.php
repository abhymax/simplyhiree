<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
// Importing related models to avoid "Class not found" errors
use App\Models\User;
use App\Models\JobCategory;
use App\Models\ExperienceLevel;
use App\Models\EducationLevel;
use App\Models\JobApplication;
use App\Models\Candidate;

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
        'category',      // Text column fallback
        'job_type_tags', 
        'is_walkin',     
        'interview_slot',
    ];

    protected $casts = [
        'application_deadline' => 'date',
        'job_type_tags' => 'array',
        'interview_slot' => 'datetime',
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
     * Kept as 'jobCategory' to avoid conflict with the 'category' text column.
     */
    public function jobCategory(): BelongsTo
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

    /**
     * Get the applications for the job.
     */
    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    // --- ACCESS CONTROL RELATIONSHIPS (FIXED TABLE NAMES) ---

    /**
     * Partners specifically ALLOWED to see this job (when visibility is 'selected').
     * Matches migration table: 'job_partner_access'
     */
    public function allowedPartners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'job_partner_access', 'job_id', 'partner_id');
    }

    /**
     * Partners specifically EXCLUDED from seeing this job.
     * Matches migration table: 'job_partner_exclusions'
     */
    public function excludedPartners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'job_partner_exclusions', 'job_id', 'partner_id');
    }

    /**
     * Candidates restricted from applying to this job.
     * Matches migration table: 'job_candidate_exclusions'
     */
    public function restrictedCandidates(): BelongsToMany
    {
        return $this->belongsToMany(Candidate::class, 'job_candidate_exclusions', 'job_id', 'candidate_id');
    }
}