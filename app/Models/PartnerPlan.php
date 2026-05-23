<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'subtitle',
        'monthly_submission_limit', 'max_team_members', 'can_view_premium_jobs',
        'price', 'price_max', 'price_suffix',
        'commission_min', 'commission_max',
        'features', 'non_features',
        'is_most_popular', 'accent_color', 'sort_order',
    ];

    protected $casts = [
        'can_view_premium_jobs' => 'boolean',
        'is_most_popular'       => 'boolean',
        'price'                 => 'decimal:2',
        'price_max'             => 'decimal:2',
        'commission_min'        => 'decimal:2',
        'commission_max'        => 'decimal:2',
        'features'              => 'array',
        'non_features'          => 'array',
    ];

    /** Formatted price for display, e.g. "₹1,999" or "₹1,999-2,999". */
    public function priceDisplay(): string
    {
        $base = '₹' . number_format((float) $this->price);
        if ($this->price_max && (float) $this->price_max > (float) $this->price) {
            return $base . '-' . number_format((float) $this->price_max);
        }
        return $base;
    }

    public function commissionDisplay(): ?string
    {
        $min = (float) $this->commission_min;
        $max = (float) $this->commission_max;
        if ($min == 0 && $max == 0) return null;
        if ($min == $max) return $min . '%';
        return $min . '-' . $max . '%';
    }
}
