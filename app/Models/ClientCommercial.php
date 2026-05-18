<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientCommercial extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'billing_type',
        'contract_data',
        'invoice_raise_days',
        'payment_terms_days',
        'is_gst_applicable',
    ];

    protected $casts = [
        'contract_data' => 'array',
        'is_gst_applicable' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
