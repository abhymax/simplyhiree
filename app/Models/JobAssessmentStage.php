<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class JobAssessmentStage extends Model
{
    protected $fillable = [
        'job_id', 'assessment_id', 'stage_order', 'next_stage_start_hours',
    ];

    protected $casts = [
        'stage_order'            => 'integer',
        'next_stage_start_hours' => 'integer',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    /**
     * Rebuild the ordered assessment stages for a job from validated form
     * input. Only assessments the given user is allowed to see are attached
     * ($scopeUser = null means admin / unrestricted). Passing an empty list
     * clears all stages.
     *
     * @param  array  $stages  list of ['assessment_id' => int, 'next_stage_start_hours' => ?int]
     */
    public static function syncForJob(Job $job, array $stages, ?User $scopeUser = null): void
    {
        $stages = array_values(array_filter($stages, function ($s) {
            return !empty($s['assessment_id']);
        }));

        // Restrict to assessments this user may attach.
        $requestedIds = collect($stages)->pluck('assessment_id')->map(fn ($id) => (int) $id)->all();
        $allowedIds = self::allowedAssessmentIds($requestedIds, $scopeUser);

        static::where('job_id', $job->id)->delete();

        $order = 1;
        $seen = [];
        foreach ($stages as $s) {
            $aid = (int) $s['assessment_id'];
            if (!in_array($aid, $allowedIds, true) || in_array($aid, $seen, true)) {
                continue; // skip disallowed or duplicate questionnaires
            }
            $seen[] = $aid;

            $hours = $s['next_stage_start_hours'] ?? null;
            $hours = ($hours === '' || $hours === null) ? null : (int) $hours;

            static::create([
                'job_id'                 => $job->id,
                'assessment_id'          => $aid,
                'stage_order'            => $order++,
                'next_stage_start_hours' => $hours,
            ]);
        }
    }

    /** @return int[] */
    private static function allowedAssessmentIds(array $requestedIds, ?User $scopeUser): array
    {
        if (empty($requestedIds)) {
            return [];
        }

        $query = Assessment::whereIn('id', $requestedIds)->where('status', 'active');
        if ($scopeUser !== null) {
            $query->visibleTo($scopeUser);
        }

        return $query->pluck('id')->map(fn ($id) => (int) $id)->all();
    }
}
