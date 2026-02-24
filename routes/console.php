<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\JobApplication;
use App\Models\User;
use App\Services\SuperadminActivityService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('superadmin:billing-due-check', function (SuperadminActivityService $activityService) {
    $count = $activityService->checkBillingDueAlerts();
    $this->info("Billing due alerts generated: {$count}");
})->purpose('Send superadmin alerts when billing period is due for joined candidates');

Artisan::command('partner:daily-pulse-whatsapp', function (SuperadminActivityService $activityService) {
    $count = $activityService->sendPartnerDailyPulseForAllActivePartners();
    $this->info("Partner daily pulse WhatsApp sent to partners: {$count}");
})->purpose('Send daily pulse WhatsApp summary to all active partners');

// ---------- Campaign Test Commands ----------
Artisan::command('wa:test:candidate-interview-scheduled {application_id}', function (
    int $applicationId,
    SuperadminActivityService $activityService
) {
    $application = JobApplication::with(['job.user', 'candidate', 'candidateUser.profile'])->find($applicationId);
    if (!$application) {
        $this->error("Application {$applicationId} not found.");
        return 1;
    }

    $activityService->sendCandidateInterviewScheduledWhatsApp($application);
    $this->info("Triggered candidate.interview_scheduled for application #{$applicationId}");
    return 0;
})->purpose('Test candidate interview scheduled WhatsApp campaign');

Artisan::command('wa:test:candidate-selected {application_id}', function (
    int $applicationId,
    SuperadminActivityService $activityService
) {
    $application = JobApplication::with(['job.user', 'candidate', 'candidateUser.profile'])->find($applicationId);
    if (!$application) {
        $this->error("Application {$applicationId} not found.");
        return 1;
    }

    $activityService->sendCandidateSelectedWhatsApp($application);
    $this->info("Triggered candidate.selected for application #{$applicationId}");
    return 0;
})->purpose('Test candidate selected WhatsApp campaign');

Artisan::command('wa:test:profile-approved {role} {user_id}', function (
    string $role,
    int $userId,
    SuperadminActivityService $activityService
) {
    $role = strtolower(trim($role));
    if (!in_array($role, ['partner', 'client'], true)) {
        $this->error("Role must be 'partner' or 'client'.");
        return 1;
    }

    $user = User::with('profile')->find($userId);
    if (!$user) {
        $this->error("User {$userId} not found.");
        return 1;
    }

    $activityService->sendProfileApprovedWhatsApp($user, $role);
    $this->info("Triggered profile.approved.{$role} for user #{$userId}");
    return 0;
})->purpose('Test partner/client profile approved WhatsApp campaign');

Artisan::command('wa:test:partner-daily-pulse {partner_id} {--selected=2} {--scheduled=3} {--turned=1}', function (
    int $partnerId,
    SuperadminActivityService $activityService
) {
    $partner = User::with('profile')->find($partnerId);
    if (!$partner || !$partner->hasRole('partner')) {
        $this->error("Partner user {$partnerId} not found.");
        return 1;
    }

    $pulse = [
        'selected' => (int) $this->option('selected'),
        'interview_scheduled' => (int) $this->option('scheduled'),
        'turned_up' => (int) $this->option('turned'),
    ];

    $activityService->sendPartnerDailyPulseWhatsApp($partner, $pulse);
    $this->info("Triggered partner.daily_pulse for partner #{$partnerId}");
    return 0;
})->purpose('Test partner daily pulse WhatsApp campaign');

Artisan::command('wa:test:billing-period-hit {application_id}', function (
    int $applicationId,
    SuperadminActivityService $activityService
) {
    $application = JobApplication::with(['job.user', 'candidate', 'candidateUser'])->find($applicationId);
    if (!$application) {
        $this->error("Application {$applicationId} not found.");
        return 1;
    }

    $result = $activityService->sendBillingReminderForSingleApplication($application);
    if (!($result['ok'] ?? false)) {
        $this->error('Billing test campaign failed.');
        $this->line(json_encode($result, JSON_PRETTY_PRINT));
        return 1;
    }

    $this->info("Triggered billing.period_hit for application #{$applicationId}");
    $this->line(json_encode($result, JSON_PRETTY_PRINT));
    return 0;
})->purpose('Test billing period hit WhatsApp campaign');

// ---------- Scheduled Jobs (IST) ----------
Schedule::command('partner:daily-pulse-whatsapp')
    ->timezone('Asia/Kolkata')
    ->dailyAt('08:30')
    ->withoutOverlapping();

Schedule::command('superadmin:billing-due-check')
    ->timezone('Asia/Kolkata')
    ->dailyAt('10:00')
    ->withoutOverlapping();
