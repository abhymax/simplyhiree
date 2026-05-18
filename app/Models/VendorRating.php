<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorRating extends Model
{
    protected $fillable = [
        'partner_id',
        'rated_by_user_id',
        'job_id',
        'application_id',
        'score',
        'speed_score',
        'quality_score',
        'communication_score',
        'feedback',
    ];

    public function partner(): BelongsTo  { return $this->belongsTo(User::class, 'partner_id'); }
    public function ratedBy(): BelongsTo  { return $this->belongsTo(User::class, 'rated_by_user_id'); }
    public function job(): BelongsTo      { return $this->belongsTo(Job::class); }
    public function application(): BelongsTo { return $this->belongsTo(JobApplication::class, 'application_id'); }

    /**
     * Recompute the denormalised aggregates on the partner user row from
     * scratch. Call after every new rating or via cron.
     */
    public static function recomputeFor(int $partnerId): void
    {
        $partner = User::find($partnerId);
        if (!$partner || !$partner->hasRole('partner')) return;

        // 1. Average rating + total
        $ratingsAgg = self::where('partner_id', $partnerId)
            ->selectRaw('AVG(score) as avg, COUNT(*) as total')
            ->first();
        $avg = $ratingsAgg && $ratingsAgg->total ? round((float)$ratingsAgg->avg, 2) : null;
        $total = (int) ($ratingsAgg->total ?? 0);

        // 2. Selection ratio: Selected hiring_status / total submitted
        $apps = JobApplication::whereHas('candidate', fn ($q) => $q->where('partner_id', $partnerId));
        $submitted = (clone $apps)->count();
        $selected  = (clone $apps)->where('hiring_status', 'Selected')->count();
        $selRatio  = $submitted > 0 ? round($selected / $submitted, 4) : null;

        // 3. Closure rate: Joined / Selected
        $joined = (clone $apps)->where('joined_status', 'Joined')->count();
        $closureRate = $selected > 0 ? round($joined / $selected, 4) : null;

        // 4. Repeat hires: distinct clients who took >= 2 hires from this partner
        $repeat = \DB::table('job_applications as ja')
            ->join('candidates as c', 'c.id', '=', 'ja.candidate_id')
            ->join('jobs as j', 'j.id', '=', 'ja.job_id')
            ->where('c.partner_id', $partnerId)
            ->where('ja.joined_status', 'Joined')
            ->selectRaw('j.user_id as client_id, COUNT(*) as hires')
            ->groupBy('j.user_id')
            ->havingRaw('hires >= 2')
            ->get()
            ->count();

        // 5. Badge logic (most generous wins)
        $badge = null;
        if ($total === 0 && $partner->created_at && $partner->created_at->gt(now()->subDays(60))) {
            $badge = 'Rising Talent';
        }
        if (($avg ?? 0) >= 4.0 && $joined >= 5) $badge = 'Top Recruiter';
        if (($avg ?? 0) >= 4.5 && $joined >= 10) $badge = 'Elite Partner';
        if ($repeat >= 3) $badge = 'Trusted Vendor';

        // 6. Vendor level (tier)
        $level = match (true) {
            ($avg ?? 0) >= 4.5 => 'Elite',
            ($avg ?? 0) >= 4.0 => 'Pro',
            ($avg ?? 0) >= 3.5 => 'Basic',
            default            => $total > 0 ? 'Restricted' : 'Basic',
        };

        $partner->update([
            'avg_rating'        => $avg,
            'total_ratings'     => $total,
            'selection_ratio'   => $selRatio,
            'closure_rate'      => $closureRate,
            'repeat_hire_count' => $repeat,
            'vendor_badge'      => $badge,
            'vendor_level'      => $level,
        ]);
    }
}
