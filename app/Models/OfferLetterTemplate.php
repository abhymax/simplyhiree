<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OfferLetterTemplate extends Model
{
    protected $fillable = ['name','subject','default_heading','body_html','is_active','sort_order'];
    protected $casts = ['is_active' => 'boolean'];
}
