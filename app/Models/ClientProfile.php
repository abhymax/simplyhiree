<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'website',
        'industry',
        'company_size',
        'description',
        'logo_path',
        'contact_person_name',
        'contact_phone',
        'gst_number',
        'address',
        'city',
        'state',
        'pincode',
        // --- NEW FIELDS ---
        'pan_number',
        'pan_file_path',
        'tan_number',
        'tan_file_path',
        'coi_number',
        'coi_file_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}