<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'job_id', 
        'candidate_user_id', 
        'candidate_id', 
        'status',
        'hiring_status',
        'interview_at',
        'client_notes',
        'joining_date',
        'joined_status', // <-- ADDED THIS
        'left_at',       // <-- ADDED THIS
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'interview_at' => 'datetime',
        'joining_date' => 'datetime',
        'left_at' => 'datetime', // <-- ADDED THIS
    ];

    /**
     * Get the job associated with the application.
     */
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Get the candidate (from the 'users' table) associated with the application.
     */
    public function candidateUser()
    {
        return $this->belongsTo(User::class, 'candidate_user_id');
    }

    /**
     * Get the candidate (from the 'candidates' table) associated with the application.
     */
    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
}