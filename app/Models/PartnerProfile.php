<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        // Profile & Bio
        'profile_picture_path',
        'company_type',
        'website',
        'linkedin_url',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'establishment_year',
        'preferred_categories',
        'preferred_locations',
        'bio',
        'address',
        'working_hours',
        // Bank
        'beneficiary_name',
        'account_number',
        'account_type',
        'ifsc_code',
        'cancelled_cheque_path',
        // PAN
        'pan_name',
        'pan_number',
        'pan_card_path',
        // GST
        'gst_number',
        'gst_certificate_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}