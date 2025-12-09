<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Import this

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'location',
        'company_name',
        'salary',
        'job_type',
        'description',
        'experience_required',
        'education_level',
        'skills_required',
        'application_deadline',
        'company_website',
        'status',
        'payout_amount',
        'minimum_stay_days',
        'openings',
        'min_age',
        'max_age',
        'gender_preference',
        'category',
        'job_type_tags',
        'is_walkin',
        'interview_slot',
        'experience_level_id',
        'education_level_id',
        'partner_visibility', // <--- Added
    ];

    protected $casts = [
        'job_type_tags' => 'array',
        'interview_slot' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        // Allow user to be null (Simplyhiree post)
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'Simplyhiree',
        ]);
    }

    // ... (Keep existing relationships: jobCategory, jobApplications, etc.) ...
    
    public function jobCategory(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }

    public function experienceLevel(): BelongsTo
    {
        return $this->belongsTo(ExperienceLevel::class);
    }

    public function educationLevel(): BelongsTo
    {
        return $this->belongsTo(EducationLevel::class);
    }

    // --- NEW ACCESS CONTROL RELATIONSHIPS ---

    /**
     * Partners specifically ALLOWED to see this job (if visibility is 'selected').
     */
    public function allowedPartners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'job_partner_access', 'job_id', 'partner_id');
    }

    /**
     * Partners excluded from this job (Legacy/Global exclusion).
     */
    public function excludedPartners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'job_partner_exclusions', 'job_id', 'partner_id');
    }

    /**
     * Candidates RESTRICTED from seeing this job.
     */
    public function restrictedCandidates(): BelongsToMany
    {
        return $this->belongsToMany(Candidate::class, 'job_candidate_exclusions', 'job_id', 'candidate_id');
    }
}