<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
        'location',
        'preferred_locations',
        'date_of_birth',
        'gender',
        'marital_status',
        'experience_status',
        'total_experience_years',
        'total_experience_months',
        'current_company',
        'current_role',
        'current_ctc',
        'expected_ctc',
        'notice_period',
        'qualification_degree',
        'specialization',
        'skills',
        'resume_path',
    ];

    protected $casts = [
        'date_of_birth'       => 'date',
        'preferred_locations' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}