<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LandingPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'status',
        'meta_title', 'meta_description',
        'logo_path', 'primary_color', 'secondary_color',
        'hero_headline', 'hero_subheadline', 'hero_image_path', 'cta_text',
        'event_date', 'event_time', 'event_platform', 'event_language',
        'seats_total', 'registration_deadline',
        'about_title', 'about_description',
        'host_name', 'host_title', 'host_bio', 'host_photo_path',
        'learnings', 'qualifications', 'benefits', 'faqs', 'form_fields',
        'footer_disclaimer',
    ];

    protected $casts = [
        'event_date'            => 'date',
        'registration_deadline' => 'datetime',
        'learnings'             => 'array',
        'qualifications'        => 'array',
        'benefits'              => 'array',
        'faqs'                  => 'array',
        'form_fields'           => 'array',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(LandingPageRegistration::class);
    }

    public function getSeatsLeftAttribute(): int
    {
        if ($this->seats_total <= 0) return 999;
        return max(0, $this->seats_total - $this->registrations()->count());
    }

    public function getPublicUrlAttribute(): string
    {
        return url('/l/' . $this->slug);
    }
}
