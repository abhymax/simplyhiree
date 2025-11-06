<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Job extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * This now includes all our new advanced fields.
     */
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
        // Payout fields
        'payout_amount',
        'minimum_stay_days',
        // New detailed fields
        'openings',
        'min_age',
        'max_age',
        'gender_preference',
        'category',
        'job_type_tags',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'job_type_tags' => 'array',
    ];

    /**
     * Get the user (client) that owns the job.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the job category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }

    // --- ADD THIS NEW FUNCTION ---
    /**
     * The partners that are excluded from this job.
     */
    public function excludedPartners()
    {
        return $this->belongsToMany(User::class, 'job_partner_exclusions', 'job_id', 'partner_id');
    }

    /**
     * Get all of the applications for this job.
     */
    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class);
    }
}

