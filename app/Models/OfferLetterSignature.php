<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OfferLetterSignature extends Model
{
    protected $fillable = ['name','designation','signature_path','is_active','sort_order'];
    protected $casts = ['is_active' => 'boolean'];
}
