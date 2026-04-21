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
        'video_url', 'video_file_path', 'video_section_title', 'video_section_description',
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

    /**
     * Convert a YouTube/Vimeo URL (watch, short, embed, youtu.be) to an embed URL.
     * Returns null if the URL doesn't look like a supported video link.
     */
    public function getEmbedUrlAttribute(): ?string
    {
        if (empty($this->video_url)) return null;
        $url = trim($this->video_url);

        // YouTube — various formats
        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/|v/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1] . '?rel=0';
        }

        // Vimeo
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }

        // Already an embed URL, leave as is
        if (str_contains($url, '/embed/') || str_contains($url, 'player.vimeo.com')) {
            return $url;
        }

        return null;
    }
}
