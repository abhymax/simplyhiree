<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\JobApplication;

class AutoForwardResumes extends Command
{
    protected $signature = 'resumes:auto-forward';

    protected $description = 'Auto-forward pending resumes to the client once auto_forward_hours has elapsed';

    public function handle(): int
    {
        $this->info('[' . now()->toDateTimeString() . '] Auto-forward sweep started');

        $applications = JobApplication::where('status', 'Pending Review')
            ->whereNull('auto_forwarded_at')
            ->whereHas('job', function ($q) {
                $q->whereNotNull('auto_forward_hours')
                  ->where('auto_forward_hours', '>', 0)
                  // Don't touch jobs that aren't actively accepting candidates.
                  ->where('status', 'approved')
                  ->whereNull('archived_at');
            })
            ->with('job:id,auto_forward_hours,status,archived_at')
            ->chunkById(200, function ($chunk) use (&$forwarded) {
                foreach ($chunk as $app) {
                    $job = $app->job;
                    if (!$job) {
                        continue;
                    }

                    $hoursPassed = $app->created_at->diffInHours(Carbon::now());
                    if ($hoursPassed < (int) $job->auto_forward_hours) {
                        continue;
                    }

                    $app->update([
                        'status'             => 'Approved',
                        'auto_forwarded_at'  => now(),
                    ]);
                    $this->line("  forwarded application #{$app->id} (job #{$job->id}, sat {$hoursPassed}h)");
                }
            });

        $count = JobApplication::whereNotNull('auto_forwarded_at')
            ->whereDate('auto_forwarded_at', today())
            ->count();

        $this->info("Auto-forward sweep done. Total auto-forwarded today: {$count}.");
        return self::SUCCESS;
    }
}
