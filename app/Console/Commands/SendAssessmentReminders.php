<?php

namespace App\Console\Commands;

use App\Models\AssessmentSession;
use App\Services\AssessmentNotifier;
use Illuminate\Console\Command;

class SendAssessmentReminders extends Command
{
    protected $signature = 'assessment:reminders {--max=3 : Max reminders per candidate} {--gap=24 : Min hours between reminders}';

    protected $description = 'Remind candidates who have not finished their assessment (email + WhatsApp).';

    public function handle(AssessmentNotifier $notifier): int
    {
        $maxReminders = (int) $this->option('max');
        $gapHours     = (int) $this->option('gap');

        $query = AssessmentSession::query()
            ->whereIn('status', ['pending', 'verified', 'in_progress'])
            ->where('reminder_count', '<', $maxReminders)
            // Give the candidate some breathing room after the invite.
            ->where('created_at', '<=', now()->subHours(6))
            // Don't remind if the link is already dead.
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) use ($gapHours) {
                $q->whereNull('last_reminded_at')
                  ->orWhere('last_reminded_at', '<=', now()->subHours($gapHours));
            });

        $sent = 0;
        $query->orderBy('id')->chunkById(100, function ($sessions) use ($notifier, &$sent) {
            foreach ($sessions as $session) {
                try {
                    $notifier->reminder($session);
                    $sent++;
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        });

        $this->info("Assessment reminders sent: {$sent}");
        return self::SUCCESS;
    }
}
