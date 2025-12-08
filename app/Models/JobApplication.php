<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; // <-- CRITICAL IMPORT

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id', 
        'candidate_user_id', 
        'candidate_id', 
        'status',
        'hiring_status',
        'interview_at',
        'client_notes',
        'joining_date',
        'joined_status',
        'left_at',
        // New Billing Fields
        'payment_status',
        'paid_at',
    ];

    protected $casts = [
        'interview_at' => 'datetime',
        'joining_date' => 'datetime',
        'left_at' => 'datetime',
        'paid_at' => 'datetime', // <-- Add this
    ];

    /**
     * Get the job associated with the application.
     */
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Get the candidate (from the 'users' table - Direct Applicants).
     */
    public function candidateUser()
    {
        return $this->belongsTo(User::class, 'candidate_user_id');
    }

    /**
     * Get the candidate (from the 'candidates' table - Agency Applicants).
     */
    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    // --- NEW ACCESSOR ---
    /**
     * Get the candidate's full name, regardless of source (User or Agency Candidate).
     * Usage: $application->candidate_name
     */
    protected function candidateName(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                // Check for Agency Candidate first
                if ($this->candidate) {
                    return $this->candidate->first_name . ' ' . $this->candidate->last_name;
                }
                
                // Check for Direct User Candidate
                if ($this->candidateUser) {
                    return $this->candidateUser->name;
                }

                return 'Unknown Candidate';
            }
        );
    }
}