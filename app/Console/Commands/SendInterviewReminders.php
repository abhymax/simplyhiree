<?php

namespace App\Console\Commands;

use App\Models\JobApplication;
use App\Services\AiSensyWhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendInterviewReminders extends Command
{
    protected $signature = 'interviews:send-reminders
        {--window=60 : Send reminders for interviews starting within N minutes from now}
        {--dry : Print what would be sent without actually firing}';

    protected $description = 'Send WhatsApp reminders to candidates whose interview is starting soon';

    public function handle(AiSensyWhatsAppService $whatsapp): int
    {
        $windowMins = (int) $this->option('window');
        $dry        = (bool) $this->option('dry');
        $now        = Carbon::now();
        $cutoff     = $now->copy()->addMinutes($windowMins);

        // Eligible: interview_at within window, no reminder sent yet
        $apps = JobApplication::with(['job', 'candidate', 'candidateUser.profile'])
            ->where('hiring_status', 'Interview Scheduled')
            ->whereNotNull('interview_at')
            ->whereBetween('interview_at', [$now, $cutoff])
            ->whereNull('interview_reminder_sent_at')
            ->get();

        if ($apps->isEmpty()) {
            $this->info("No interviews in the next {$windowMins} minutes need reminders.");
            return self::SUCCESS;
        }

        $sent = 0; $skipped = 0; $failed = 0;

        foreach ($apps as $app) {
            $cand  = $app->candidate;
            $name  = $cand ? trim(($cand->first_name??'').' '.($cand->last_name??'')) : ($app->candidateUser?->name ?? 'Candidate');
            $phone = $cand?->phone ?: optional($app->candidateUser?->profile)->phone_number;

            if (!$phone) {
                $this->warn("Skipping {$name} (app #{$app->id}) — no phone");
                $skipped++;
                continue;
            }

            $time = $app->interview_at->format('h:i A');
            $link = $app->meeting_link ?: ($app->interview_location ?: 'TBD');
            $job  = $app->job?->title ?? 'the role';

            $message  = "Hi {$name}, this is a reminder for your interview at {$time} today for {$job}. ";
            $message .= $app->meeting_link
                ? "Join here: {$app->meeting_link}"
                : ($app->interview_location ? "Location: {$app->interview_location}" : "Please check your email for joining details.");

            if ($dry) {
                $this->line("DRY → {$phone}: " . substr($message, 0, 100) . '...');
                continue;
            }

            try {
                $res = $whatsapp->sendEventAlert(
                    $phone,
                    'interview_reminder',
                    'Interview reminder',
                    $message,
                    ['template_params' => [$name, $job, $time, $link]]
                );
                if ($res['ok'] ?? false) {
                    $app->update(['interview_reminder_sent_at' => now()]);
                    $sent++;
                    $this->info("✓ {$name} ({$phone})");
                } else {
                    $failed++;
                    $this->warn("✗ {$name}: " . ($res['error'] ?? 'unknown'));
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->error("✗ {$name}: " . $e->getMessage());
            }
        }

        $this->info("Done. Sent: {$sent}, Skipped: {$skipped}, Failed: {$failed}");
        return self::SUCCESS;
    }
}
