<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use App\Notifications\SuperadminActivityNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SuperadminActivityService
{
    public function __construct(private readonly AiSensyWhatsAppService $whatsApp)
    {
    }

    public function logEvent(
        string $eventKey,
        string $title,
        string $message,
        string $icon = 'check-circle',
        ?Model $subject = null,
        array $metadata = [],
        ?User $actor = null
    ): void {
        if (!Schema::hasTable('admin_activity_logs')) {
            return;
        }

        $actorUser = $actor ?: Auth::user();

        AdminActivityLog::create([
            'event_key' => $eventKey,
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'actor_id' => $actorUser?->id,
            'actor_name' => $actorUser?->name,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'metadata' => $metadata,
            'whatsapp_status' => 'skipped',
            'occurred_at' => now(),
        ]);

        if (!Schema::hasTable('notifications')) {
            return;
        }

        $superadmins = User::role('Superadmin')->get();
        foreach ($superadmins as $superadmin) {
            $superadmin->notify(new SuperadminActivityNotification(
                eventKey: $eventKey,
                title: $title,
                message: $message,
                icon: $icon,
                extra: [
                    'subject_type' => $subject ? $subject::class : null,
                    'subject_id' => $subject?->getKey(),
                    'metadata' => $metadata,
                ]
            ));
        }
    }

    public function logUserSignup(User $user, string $role, string $source = 'web'): void
    {
        $title = 'New ' . ucfirst($role) . ' Signup';
        $message = "{$user->name} ({$user->email}) registered as {$role} via {$source}.";

        $this->logEvent(
            eventKey: "signup.{$role}",
            title: $title,
            message: $message,
            icon: 'user-check',
            subject: $user,
            metadata: [
                'source' => $source,
                'role' => $role,
                'user_id' => $user->id,
                'status' => $user->status,
            ]
        );
    }

    public function logClientJobPosted(Job $job): void
    {
        $clientName = $job->user?->name ?? 'Unknown Client';
        $this->logEvent(
            eventKey: 'client.job_posted',
            title: 'Client Posted New Job',
            message: "{$clientName} posted '{$job->title}' ({$job->location}).",
            icon: 'check-circle',
            subject: $job,
            metadata: [
                'job_id' => $job->id,
                'client_id' => $job->user_id,
                'job_status' => $job->status,
            ]
        );
    }

    public function logApplicationLifecycle(JobApplication $application, string $eventKey): void
    {
        $application->loadMissing(['job.user', 'candidate', 'candidateUser']);

        $candidateName = $this->candidateName($application);
        $jobTitle = (string) ($application->job?->title ?? 'Unknown Job');
        $clientName = (string) ($application->job?->user?->name ?? 'Unknown Client');

        $map = [
            'client.candidate_approved' => [
                'title' => 'Client Approved Candidate',
                'message' => "{$clientName} approved {$candidateName} for {$jobTitle}.",
                'icon' => 'check-circle',
            ],
            'client.interview_scheduled' => [
                'title' => 'Interview Scheduled',
                'message' => "{$clientName} scheduled interview for {$candidateName} ({$jobTitle}).",
                'icon' => 'calendar-event',
            ],
            'client.candidate_selected' => [
                'title' => 'Candidate Selected',
                'message' => "{$clientName} selected {$candidateName} for {$jobTitle}.",
                'icon' => 'check-circle',
            ],
            'candidate.left_company' => [
                'title' => 'Candidate Left Company',
                'message' => "{$candidateName} left {$jobTitle} at {$clientName}.",
                'icon' => 'user-clock',
            ],
            'candidate.joined_company' => [
                'title' => 'Candidate Joined Company',
                'message' => "{$candidateName} joined {$jobTitle} at {$clientName}.",
                'icon' => 'user-check',
            ],
        ];

        if (!isset($map[$eventKey])) {
            return;
        }

        $this->logEvent(
            eventKey: $eventKey,
            title: $map[$eventKey]['title'],
            message: $map[$eventKey]['message'],
            icon: $map[$eventKey]['icon'],
            subject: $application,
            metadata: [
                'application_id' => $application->id,
                'job_id' => $application->job_id,
                'client_id' => $application->job?->user_id,
                'candidate_user_id' => $application->candidate_user_id,
                'candidate_id' => $application->candidate_id,
                'hiring_status' => $application->hiring_status,
                'joined_status' => $application->joined_status,
            ]
        );
    }

    public function sendForgotPasswordTemporaryPassword(User $user, string $phoneNumber, string $temporaryPassword): array
    {
        $normalized = $this->whatsApp->normalizeIndianPhone($phoneNumber);
        if (!$normalized) {
            return ['ok' => false, 'error' => 'INVALID_PHONE'];
        }

        $message = "Your temporary password is {$temporaryPassword}. Please login and change it immediately.";
        $baseMeta = [
            'user_name' => $user->name ?: 'SimplyHiree User',
            'user_id' => $user->id,
            'role' => $user->getRoleNames()->first(),
        ];

        // Try common template parameter shapes to avoid campaign/template mismatch.
        $paramAttempts = [
            [$temporaryPassword],
            [$user->name ?: 'User', $temporaryPassword],
            [$temporaryPassword, now()->format('d M Y, h:i A')],
            [$user->name ?: 'User', $temporaryPassword, now()->format('d M Y, h:i A')],
            [],
        ];

        $last = ['ok' => false, 'error' => 'SEND_FAILED'];
        foreach ($paramAttempts as $params) {
            $meta = $baseMeta;
            if (!empty($params)) {
                $meta['template_params'] = $params;
            }

            $last = $this->whatsApp->sendEventAlert(
                destination: $normalized,
                eventKey: 'auth.forgot_password',
                title: 'SimplyHiree Password Reset',
                message: $message,
                metadata: $meta
            );

            if (($last['ok'] ?? false) === true) {
                return $last;
            }

            $response = strtolower((string) ($last['response'] ?? ''));
            $isParamMismatch = str_contains($response, 'template params does not match the campaign');
            if (!$isParamMismatch) {
                // For non-parameter errors, stop retrying and return immediately.
                return $last;
            }
        }

        return $last;
    }

    public function sendCandidateInterviewScheduledWhatsApp(JobApplication $application): void
    {
        $application->loadMissing(['job.user', 'candidate', 'candidateUser.profile']);

        $candidatePhone = $this->candidatePhone($application);
        if (!$candidatePhone) {
            return;
        }

        $jobTitle = (string) ($application->job?->title ?? 'your applied job');
        $clientName = (string) ($application->job?->user?->name ?? 'the client');
        $when = $application->interview_at
            ? Carbon::parse($application->interview_at)->format('d M Y, h:i A')
            : 'soon';

        $this->sendWhatsAppToPhones(
            [$candidatePhone],
            'candidate.interview_scheduled',
            'Interview Scheduled',
            "Your interview for {$jobTitle} with {$clientName} is scheduled on {$when}.",
            ['application_id' => $application->id, 'job_id' => $application->job_id]
        );
    }

    public function sendCandidateSelectedWhatsApp(JobApplication $application): void
    {
        $application->loadMissing(['job.user', 'candidate', 'candidateUser.profile']);

        $candidatePhone = $this->candidatePhone($application);
        if (!$candidatePhone) {
            return;
        }

        $jobTitle = (string) ($application->job?->title ?? 'your applied job');
        $clientName = (string) ($application->job?->user?->name ?? 'the client');
        $joining = $application->joining_date
            ? Carbon::parse($application->joining_date)->format('d M Y')
            : 'as communicated by the client';

        $this->sendWhatsAppToPhones(
            [$candidatePhone],
            'candidate.selected',
            'Congratulations! You Are Selected',
            "You have been selected for {$jobTitle} with {$clientName}. Joining date: {$joining}.",
            ['application_id' => $application->id, 'job_id' => $application->job_id]
        );
    }

    public function sendProfileApprovedWhatsApp(User $user, string $role): void
    {
        $user->loadMissing('profile');

        $normalized = $this->whatsApp->normalizeIndianPhone($user->profile?->phone_number);
        if (!$normalized) {
            return;
        }

        $this->sendWhatsAppToPhones(
            [$normalized],
            "profile.approved.{$role}",
            'Profile Approved',
            "Your SimplyHiree {$role} profile has been approved. You can login now.",
            ['user_id' => $user->id, 'role' => $role]
        );
    }

    public function sendPartnerDailyPulseWhatsApp(User $partner, array $pulse): void
    {
        $partner->loadMissing('profile');

        $normalized = $this->whatsApp->normalizeIndianPhone($partner->profile?->phone_number);
        if (!$normalized) {
            return;
        }

        $selected = (int) ($pulse['selected'] ?? 0);
        $scheduled = (int) ($pulse['interview_scheduled'] ?? 0);
        $turnedUp = (int) ($pulse['turned_up'] ?? 0);

        $this->sendWhatsAppToPhones(
            [$normalized],
            'partner.daily_pulse',
            'Daily Pulse Summary',
            "Today: Selected {$selected}, Interview Scheduled {$scheduled}, Turned Up {$turnedUp}.",
            ['partner_id' => $partner->id]
        );
    }

    public function sendPartnerDailyPulseForAllActivePartners(): int
    {
        $partners = User::role('partner')->where('status', 'active')->get();
        $today = Carbon::today();

        foreach ($partners as $partner) {
            $base = JobApplication::query()
                ->whereHas('candidate', function ($q) use ($partner) {
                    $q->where('partner_id', $partner->id);
                });

            $pulse = [
                'selected' => (clone $base)
                    ->where('hiring_status', 'Selected')
                    ->whereDate('updated_at', $today)
                    ->count(),
                'interview_scheduled' => (clone $base)
                    ->where('hiring_status', 'Interview Scheduled')
                    ->whereDate('updated_at', $today)
                    ->count(),
                'turned_up' => (clone $base)
                    ->where('hiring_status', 'Interviewed')
                    ->whereDate('updated_at', $today)
                    ->count(),
            ];

            $this->sendPartnerDailyPulseWhatsApp($partner, $pulse);

            $this->logEvent(
                eventKey: 'partner.daily_pulse',
                title: 'Partner Daily Pulse Sent',
                message: "Daily pulse sent to {$partner->name}: Selected {$pulse['selected']}, Scheduled {$pulse['interview_scheduled']}, Turned Up {$pulse['turned_up']}.",
                icon: 'calendar-event',
                subject: $partner,
                metadata: $pulse
            );
        }

        return $partners->count();
    }

    public function checkBillingDueAlerts(): int
    {
        if (!Schema::hasTable('job_applications') || !Schema::hasColumn('job_applications', 'billing_due_alerted_at')) {
            return 0;
        }

        $count = 0;

        $applications = JobApplication::query()
            ->with(['job.user', 'candidate', 'candidateUser'])
            ->whereNotNull('joining_date')
            ->whereNull('billing_due_alerted_at')
            ->where('joined_status', 'Joined')
            ->where(function ($query) {
                $query->whereNull('payment_status')
                    ->orWhere('payment_status', '!=', 'paid');
            })
            ->get();

        foreach ($applications as $application) {
            if (!$application->job || !$application->job->user) {
                continue;
            }

            $billableDays = (int) ($application->job->user->billable_period_days ?? 30);
            $invoiceDate = Carbon::parse($application->joining_date)->addDays($billableDays);

            if ($invoiceDate->isFuture()) {
                continue;
            }

            $candidateName = $this->candidateName($application);
            $clientName = (string) ($application->job->user->name ?? 'Unknown Client');
            $jobTitle = (string) ($application->job->title ?? 'Unknown Job');

            $this->logEvent(
                eventKey: 'billing.period_hit',
                title: 'Billing Period Hit',
                message: "Billing due for {$candidateName} ({$jobTitle}) under {$clientName}.",
                icon: 'user-clock',
                subject: $application,
                metadata: [
                    'application_id' => $application->id,
                    'invoice_date' => $invoiceDate->toDateString(),
                    'billable_days' => $billableDays,
                    'client_id' => $application->job->user_id,
                    'job_id' => $application->job_id,
                ]
            );

            $this->sendBillingReminderToSuperadmins($application, $invoiceDate, $billableDays);

            $application->billing_due_alerted_at = now();
            $application->saveQuietly();
            $count++;
        }

        return $count;
    }

    public function sendBillingReminderForSingleApplication(JobApplication $application): array
    {
        $application->loadMissing(['job.user', 'candidate', 'candidateUser']);

        if (!$application->job || !$application->job->user) {
            return ['ok' => false, 'error' => 'APPLICATION_RELATION_MISSING'];
        }

        $phones = $this->resolveSuperadminPhones();
        if (empty($phones)) {
            return ['ok' => false, 'error' => 'NO_SUPERADMIN_PHONE'];
        }

        $billableDays = (int) ($application->job->user->billable_period_days ?? 30);
        $invoiceDate = $application->joining_date
            ? Carbon::parse($application->joining_date)->addDays($billableDays)
            : Carbon::today();

        $candidateName = $this->candidateName($application);
        $jobTitle = (string) ($application->job?->title ?? 'Unknown Job');
        $clientName = (string) ($application->job?->user?->name ?? 'Unknown Client');

        $message = "Raise invoice now: {$candidateName}, {$jobTitle}, {$clientName}. Billing date: {$invoiceDate->format('d M Y')} ({$billableDays} days).";

        $sent = 0;
        $failed = 0;
        $last = null;

        foreach ($phones as $phone) {
            $result = $this->whatsApp->sendEventAlert(
                destination: $phone,
                eventKey: 'billing.period_hit',
                title: 'Invoice Reminder',
                message: $message,
                metadata: [
                    'application_id' => $application->id,
                    'job_id' => $application->job_id,
                    'client_id' => $application->job?->user_id,
                    'candidate_user_id' => $application->candidate_user_id,
                ]
            );

            $last = $result;
            if (($result['ok'] ?? false) === true) {
                $sent++;
            } else {
                $failed++;
            }
        }

        return [
            'ok' => $failed === 0,
            'sent' => $sent,
            'failed' => $failed,
            'phones' => $phones,
            'last' => $last,
        ];
    }

    private function sendBillingReminderToSuperadmins(JobApplication $application, Carbon $invoiceDate, int $billableDays): void
    {
        $application->loadMissing(['job.user', 'candidate', 'candidateUser']);

        $phones = $this->resolveSuperadminPhones();
        if (empty($phones)) {
            return;
        }

        $candidateName = $this->candidateName($application);
        $jobTitle = (string) ($application->job?->title ?? 'Unknown Job');
        $clientName = (string) ($application->job?->user?->name ?? 'Unknown Client');

        $this->sendWhatsAppToPhones(
            $phones,
            'billing.period_hit',
            'Invoice Reminder',
            "Raise invoice now: {$candidateName}, {$jobTitle}, {$clientName}. Billing date: {$invoiceDate->format('d M Y')} ({$billableDays} days).",
            ['application_id' => $application->id, 'job_id' => $application->job_id]
        );
    }

    private function candidateName(JobApplication $application): string
    {
        if ($application->candidate) {
            $first = trim((string) $application->candidate->first_name);
            $last = trim((string) $application->candidate->last_name);
            $full = trim("{$first} {$last}");
            if ($full !== '') {
                return $full;
            }
        }

        return (string) ($application->candidateUser?->name ?? 'Unknown Candidate');
    }

    private function candidatePhone(JobApplication $application): ?string
    {
        $raw = $application->candidate?->phone_number ?? $application->candidateUser?->profile?->phone_number;
        return $this->whatsApp->normalizeIndianPhone($raw);
    }

    private function resolveSuperadminPhones(): array
    {
        $phones = [];

        $superadmins = User::role('Superadmin')->with('profile')->get();
        foreach ($superadmins as $superadmin) {
            $normalized = $this->whatsApp->normalizeIndianPhone($superadmin->profile?->phone_number);
            if ($normalized) {
                $phones[] = $normalized;
            }
        }

        $fallbackList = explode(',', (string) config('services.aisensy.superadmin_phones', ''));
        foreach ($fallbackList as $phone) {
            $normalized = $this->whatsApp->normalizeIndianPhone(trim($phone));
            if ($normalized) {
                $phones[] = $normalized;
            }
        }

        return array_values(array_unique($phones));
    }

    private function sendWhatsAppToPhones(array $phones, string $eventKey, string $title, string $message, array $metadata = []): void
    {
        foreach ($phones as $phone) {
            $this->whatsApp->sendEventAlert(
                destination: $phone,
                eventKey: $eventKey,
                title: $title,
                message: $message,
                metadata: $metadata
            );
        }
    }
}
