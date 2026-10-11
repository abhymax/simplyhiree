<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ApplicationActivity extends Model
{
    protected $table = 'application_activities';

    protected $fillable = [
        'job_application_id', 'user_id', 'actor_name', 'actor_side', 'action', 'detail',
    ];

    /** Human labels for the badge shown beside a listing. */
    public const LABELS = [
        'submitted'           => 'Submitted',
        'approved'            => 'Approved',
        'rejected_by_admin'   => 'Rejected',
        'shortlisted'         => 'Shortlisted',
        'maybe'               => 'Marked maybe',
        'rejected'            => 'Rejected',
        'review_cleared'      => 'Review undone',
        'interview_scheduled' => 'Interview scheduled',
        'interview_cancelled' => 'Interview cancelled',
        'interview_resent'    => 'Invite re-sent',
        'marked_appeared'     => 'Marked appeared',
        'marked_noshow'       => 'Marked no-show',
        'feedback_added'      => 'Feedback added',
        'selected'            => 'Selected',
        'marked_joined'       => 'Marked joined',
        'marked_not_joined'   => 'Marked not joined',
        'marked_left'         => 'Marked left',
    ];

    public function application() { return $this->belongsTo(JobApplication::class, 'job_application_id'); }
    public function user()        { return $this->belongsTo(User::class, 'user_id'); }

    public function label(): string
    {
        return self::LABELS[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }

    /** Record an action against an application. Never throws into the caller. */
    public static function log($application, string $action, ?string $detail = null): void
    {
        try {
            $id = is_object($application) ? ($application->id ?? null) : $application;
            if (!$id) {
                return;
            }

            $user = Auth::user();

            static::create([
                'job_application_id' => $id,
                'user_id'            => $user?->id,
                'actor_name'         => $user?->name,
                'actor_side'         => static::sideFor($user),
                'action'             => $action,
                'detail'             => $detail,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Activity log failed: ' . $e->getMessage());
        }
    }

    private static function sideFor(?User $user): ?string
    {
        if (!$user) return null;
        if ($user->hasRole('Superadmin') || $user->hasRole('Manager')) return 'simplyhiree';
        if ($user->hasRole('client'))  return 'client';
        if ($user->hasRole('partner')) return 'vendor';
        return 'candidate';
    }
}
