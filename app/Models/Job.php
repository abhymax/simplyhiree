<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'min_experience',      // <--- ADD THIS
    'max_experience',      // <--- ADD THIS
        'education_level_id',
        'application_deadline',
        'status',
        'deactivation_requested_at',
        'deactivation_reason',
        'archived_at',
        'archived_by_role',
        'archived_by_user_id',
        // Admin / Billing fields
        'payout_amount',
        'minimum_stay_days',
        'client_payout_amount',
        'client_payout_days',
        'replacement_guarantee_days',
        'partner_visibility',
        'is_premium',
        // Finance (per-job billing engine)
        'fee_type',
        'fee_amount',
        'invoice_release_days',
        'replacement_period_days',
        // Screening & vendor controls
        'screening_required',
        'auto_forward_hours',
        'max_resume_per_vendor',
        'resume_submission_deadline',
        // Staffing & confidentiality
        'is_company_confidential',
        'staffing_model',
        'contract_billing_cycle',
        'contract_margin_type',
        'contract_payroll_managed_by',
        'rpo_monthly_retainer',
        'rpo_per_position_fee',
        'rpo_dedicated_recruiter_cost',
        // Advanced fields
        'skills_required',
        'company_website',
        'openings',
        'min_age',
        'max_age',
        'gender_preference',
        'category',       // Text column fallback
        'job_type_tags', 
        'is_walkin',      
        'interview_slot',
    ];

    protected $casts = [
        'application_deadline'         => 'date',
        'job_type_tags'                => 'array',
        'interview_slot'               => 'datetime',
        'deactivation_requested_at'    => 'datetime',
        'archived_at'                  => 'datetime',
        'resume_submission_deadline'   => 'datetime',
        'screening_required'           => 'boolean',
        'is_company_confidential'      => 'boolean',
        'is_premium'                   => 'boolean',
        'fee_amount'                   => 'decimal:2',
        'rpo_monthly_retainer'         => 'decimal:2',
        'rpo_per_position_fee'         => 'decimal:2',
        'rpo_dedicated_recruiter_cost' => 'decimal:2',
    ];

    /**
     * Get the user (Client) that owns the job.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the job category (Original Method).
     * Kept to ensure Partner Dashboard keeps working.
     */
    public function jobCategory(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }

    /**
     * Get the job category (Alias Method).
     * ADDED: This supports the Admin Controller which calls 'category'.
     */
    public function category(): BelongsTo
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

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by_user_id');
    }

    /**
     * Render description as safe HTML. New descriptions come from the rich
     * editor as already-sanitized HTML; legacy plain-text descriptions get
     * escaped and have newlines converted to <br>.
     */
    public function getFormattedDescriptionAttribute(): string
    {
        $raw = (string) ($this->description ?? '');
        if ($raw === '') return '';
        $hasHtml = preg_match('/<\/?(p|br|ul|ol|li|h[1-6]|strong|b|em|i|u|blockquote|a|span)\b/i', $raw) === 1;
        if ($hasHtml) {
            $allowed = '<p><br><b><strong><i><em><u><s><strike><ul><ol><li><h2><h3><blockquote><a><span>';
            $clean = strip_tags($raw, $allowed);
            $clean = preg_replace('/\s+on[a-z]+\s*=\s*"(?:[^"\\\\]|\\\\.)*"/i', '', $clean);
            $clean = preg_replace("/\s+on[a-z]+\s*=\s*'(?:[^'\\\\]|\\\\.)*'/i", '', $clean);
            $clean = preg_replace('/href\s*=\s*"\s*javascript:[^"]*"/i', 'href="#"', $clean);
            return $clean;
        }
        return nl2br(e($raw));
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

    protected function jobCode(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->id ? sprintf('SH-JOB-%06d', (int) $this->id) : null,
        );
    }

    protected function formattedExperience(): Attribute
    {
        return Attribute::make(
            get: function () {
                $min = $this->min_experience;
                $max = $this->max_experience;

                if ($min !== null || $max !== null) {
                    if ($min !== null && $max !== null) {
                        if ((int) $min === (int) $max) {
                            return $min . ' Year' . ((int) $min === 1 ? '' : 's');
                        }

                        return $min . '-' . $max . ' Years';
                    }

                    if ($min !== null) {
                        return $min . '+ Years';
                    }

                    return 'Up to ' . $max . ' Years';
                }

                return $this->experienceLevel?->name ?? 'Any';
            },
        );
    }
}
