<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Updated to match the real database schema.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'job_id',
        'candidate_user_id', // For candidates who are users
        'candidate_id',      // For candidates from a partner's pool
        'status',
    ];

    /**
     * Get the job this application is for.
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Get the candidate (from partner pool) this application is for.
     */
    public function candidate(): BelongsTo
    {
        // This links to the 'candidates' table
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    /**
     * Get the partner (user) who submitted this application.
     * This relationship works by going THROUGH the candidate.
     */
   /* public function partner()
    {
        // An application belongs to a partner THROUGH its candidate
        return $this->hasOneThrough(
            User::class,      // The final model we want (Partner, which is a User)
            Candidate::class, // The intermediate model (Candidate)
            'id',             // Foreign key on Candidate table (candidates.id)
            'id',             // Foreign key on User table (users.id)
            'candidate_id',   // Local key on JobApplication table (job_applications.candidate_id)
            'partner_id'      // Local key on Candidate table (candidates.partner_id)
        );
    }*/

    /**
     * Get the candidate (who is a user) this application is for.
     */
    public function candidateUser(): BelongsTo
    {
        // This links to the 'users' table
        return $this->belongsTo(User::class, 'candidate_user_id');
    }
}