<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DuplicateCandidateService
{
    public function __construct(private readonly SuperadminActivityService $activityService)
    {
    }

    public function fingerprint(?UploadedFile $file): ?string
    {
        return $file?->isValid() ? hash_file('sha256', $file->getRealPath()) : null;
    }

    public function assess(int $partnerId, ?string $email, ?string $phone, ?string $fingerprint = null, ?int $excludeId = null, bool $onlyEarlier = false): array
    {
        $email = $this->normalizeEmail($email);
        $phone = $this->normalizePhone($phone);
        $query = Candidate::query()->where('partner_id', '!=', $partnerId);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
            if ($onlyEarlier) $query->where('id', '<', $excludeId);
        }

        $matches = $query->get(['id', 'partner_id', 'email', 'phone_number', 'resume_fingerprint']);
        $match = $matches->first(function (Candidate $candidate) use ($email, $phone, $fingerprint) {
            return ($email && $this->normalizeEmail($candidate->email) === $email)
                || ($phone && $this->normalizePhone($candidate->phone_number) === $phone)
                || ($fingerprint && hash_equals((string) $candidate->resume_fingerprint, $fingerprint));
        });
        if (!$match) return ['is_duplicate' => false, 'match' => null, 'reasons' => []];

        $reasons = [];
        if ($email && $this->normalizeEmail($match->email) === $email) $reasons[] = 'email';
        if ($phone && $this->normalizePhone($match->phone_number) === $phone) $reasons[] = 'phone';
        if ($fingerprint && $match->resume_fingerprint && hash_equals($match->resume_fingerprint, $fingerprint)) $reasons[] = 'resume';
        return ['is_duplicate' => true, 'match' => $match, 'reasons' => $reasons];
    }

    public function quarantine(Candidate $candidate, array $assessment, string $source): void
    {
        if (!$assessment['is_duplicate']) return;
        $wasPending = $candidate->duplicate_status === 'pending_review';
        $candidate->update([
            'duplicate_status' => 'pending_review',
            'duplicate_of_candidate_id' => $assessment['match']->id,
            'duplicate_reasons' => $assessment['reasons'],
            'duplicate_reviewed_by' => null,
            'duplicate_reviewed_at' => null,
        ]);
        if (!$wasPending && $source !== 'backfill') {
            $this->activityService->logEvent(
                'risk.candidate_duplicate',
                'Candidate quarantined for duplicate review',
                trim($candidate->first_name.' '.$candidate->last_name).' matched candidate #'.$assessment['match']->id.' by '.implode(', ', $assessment['reasons']).'.',
                'triangle-exclamation',
                $candidate,
                ['source' => $source, 'matched_candidate_id' => $assessment['match']->id, 'reasons' => $assessment['reasons']]
            );
        }
    }

    public function submissionConflict(Candidate $candidate, Job $job): ?Candidate
    {
        $email = $this->normalizeEmail($candidate->email);
        $phone = $this->normalizePhone($candidate->phone_number);
        return JobApplication::where('job_id', $job->id)->whereNotNull('candidate_id')->with('candidate')
            ->get()->pluck('candidate')->filter()->first(function (Candidate $existing) use ($candidate, $email, $phone) {
                if ($existing->id === $candidate->id) return true;
                return ($email && $this->normalizeEmail($existing->email) === $email)
                    || ($phone && $this->normalizePhone($existing->phone_number) === $phone)
                    || ($candidate->resume_fingerprint && $existing->resume_fingerprint && hash_equals($existing->resume_fingerprint, $candidate->resume_fingerprint));
            });
    }

    public function quarantineForJobConflict(Candidate $candidate, Candidate $existing, Job $job): void
    {
        if ($candidate->id === $existing->id) return;
        $this->quarantine($candidate, [
            'is_duplicate' => true,
            'match' => $existing,
            'reasons' => ['same_job'],
        ], 'job_submission:'.$job->id);
    }

    /** A Superadmin explicitly released this candidate; same-job checks must not re-quarantine it. */
    public function isReleased(Candidate $candidate): bool
    {
        return $candidate->duplicate_status === 'genuine' && !empty($candidate->duplicate_reviewed_by);
    }

    /** Remember which job a blocked submission was for, so a release can complete it. */
    public function rememberBlockedJob(Candidate $candidate, Job $job): void
    {
        if ((int) $candidate->duplicate_blocked_job_id !== (int) $job->id) {
            $candidate->forceFill(['duplicate_blocked_job_id' => $job->id])->save();
        }
    }

    public function canSubmit(Candidate $candidate): bool
    {
        return in_array($candidate->duplicate_status, ['clear', 'genuine'], true);
    }

    public function backfillExisting(): array
    {
        $fingerprinted = 0;
        Candidate::whereNotNull('resume_path')->whereNull('resume_fingerprint')->orderBy('id')->each(function (Candidate $candidate) use (&$fingerprinted) {
            if (!Storage::disk('public')->exists($candidate->resume_path)) return;
            $candidate->update(['resume_fingerprint' => hash_file('sha256', Storage::disk('public')->path($candidate->resume_path))]);
            $fingerprinted++;
        });

        $quarantined = 0;
        Candidate::where('duplicate_status', 'clear')->orderBy('id')->each(function (Candidate $candidate) use (&$quarantined) {
            $assessment = $this->assess($candidate->partner_id, $candidate->email, $candidate->phone_number, $candidate->resume_fingerprint, $candidate->id, true);
            if (!$assessment['is_duplicate']) return;
            $this->quarantine($candidate, $assessment, 'backfill');
            $quarantined++;
        });
        return compact('fingerprinted', 'quarantined');
    }

    private function normalizeEmail(?string $email): ?string
    {
        $value = strtolower(trim((string) $email));
        return $value !== '' ? $value : null;
    }

    private function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if (strlen($digits) > 10) $digits = substr($digits, -10);
        return $digits !== '' ? $digits : null;
    }
}
