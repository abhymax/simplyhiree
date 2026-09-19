<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AssessmentSession extends Model
{
    /** OTP validity + resend/verify guards. */
    public const OTP_TTL_MINUTES   = 10;
    public const OTP_MAX_ATTEMPTS  = 5;
    public const OTP_RESEND_SECONDS = 60;
    public const LINK_TTL_DAYS     = 14;

    protected $fillable = [
        'job_id', 'job_application_id', 'candidate_id', 'partner_id',
        'token', 'email', 'candidate_name',
        'otp_hash', 'otp_expires_at', 'otp_attempts', 'otp_last_sent_at', 'verified_at',
        'current_stage', 'status', 'expires_at',
    ];

    protected $casts = [
        'otp_expires_at'   => 'datetime',
        'otp_last_sent_at' => 'datetime',
        'verified_at'      => 'datetime',
        'expires_at'       => 'datetime',
        'otp_attempts'     => 'integer',
        'current_stage'    => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $session) {
            if (empty($session->token)) {
                $session->token = self::newToken();
            }
            if (empty($session->expires_at)) {
                $session->expires_at = now()->addDays(self::LINK_TTL_DAYS);
            }
        });
    }

    public static function newToken(): string
    {
        do {
            $token = Str::random(48);
        } while (static::where('token', $token)->exists());

        return $token;
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    // --- Email OTP ---------------------------------------------------------

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function isLinkExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function canResendOtp(): bool
    {
        return $this->otp_last_sent_at === null
            || $this->otp_last_sent_at->addSeconds(self::OTP_RESEND_SECONDS)->isPast();
    }

    /** Generate a fresh 6-digit OTP, store its hash, and return the plain code. */
    public function generateOtp(): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->forceFill([
            'otp_hash'         => Hash::make($code),
            'otp_expires_at'   => now()->addMinutes(self::OTP_TTL_MINUTES),
            'otp_attempts'     => 0,
            'otp_last_sent_at' => now(),
        ])->save();

        return $code;
    }

    /**
     * Verify a submitted OTP. Returns one of:
     *   'ok' | 'expired' | 'locked' | 'invalid'
     */
    public function verifyOtp(string $code): string
    {
        if ($this->otp_hash === null || $this->otp_expires_at === null) {
            return 'expired';
        }
        if ($this->otp_expires_at->isPast()) {
            return 'expired';
        }
        if ($this->otp_attempts >= self::OTP_MAX_ATTEMPTS) {
            return 'locked';
        }

        if (!Hash::check($code, $this->otp_hash)) {
            $this->increment('otp_attempts');
            return $this->otp_attempts >= self::OTP_MAX_ATTEMPTS ? 'locked' : 'invalid';
        }

        $this->forceFill([
            'verified_at'    => now(),
            'otp_hash'       => null,
            'otp_expires_at' => null,
            'otp_attempts'   => 0,
            'status'         => $this->status === 'pending' ? 'verified' : $this->status,
        ])->save();

        return 'ok';
    }

    public function maskedEmail(): string
    {
        $email = (string) $this->email;
        if (!str_contains($email, '@')) {
            return $email;
        }
        [$local, $domain] = explode('@', $email, 2);
        $keep = mb_substr($local, 0, 1);
        $masked = $keep . str_repeat('*', max(1, mb_strlen($local) - 1));

        return $masked . '@' . $domain;
    }
}
