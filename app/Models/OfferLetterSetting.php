<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OfferLetterSetting extends Model
{
    protected $fillable = ['director_name','director_designation','signature_path','logo_path','company_address','company_footer'];
    /** The single settings row (created on first access). */
    public static function current(): self
    {
        return static::first() ?? static::create([
            'director_name' => 'Aman Yadav',
            'director_designation' => 'Director',
            'company_address' => 'SimplyHiree',
        ]);
    }
}
