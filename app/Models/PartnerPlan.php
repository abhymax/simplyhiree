<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'monthly_submission_limit',
        'max_team_members',
        'can_view_premium_jobs',
        'price',
    ];

    protected $casts = [
        'can_view_premium_jobs' => 'boolean',
        'price' => 'decimal:2',
    ];
}
