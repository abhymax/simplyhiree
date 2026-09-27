<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PlanService;
use Illuminate\Console\Command;

class ExpirePartnerPlans extends Command
{
    protected $signature = 'plans:expire';
    protected $description = 'Downgrade expired partner plans to Free and send daily expiry reminders (last 7 days).';

    public function handle(PlanService $plans): int
    {
        $now = now();
        $downgraded = 0; $reminded = 0;

        // 1) Expired -> downgrade to Free
        User::whereNotNull('plan_expires_at')
            ->where('plan_expires_at', '<', $now)
            ->whereNotNull('partner_plan')
            ->where('partner_plan', '!=', 'Free')
            ->orderBy('id')
            ->chunkById(100, function ($owners) use ($plans, &$downgraded) {
                foreach ($owners as $o) {
                    try { $plans->downgradeToFree($o); $downgraded++; }
                    catch (\Throwable $e) { report($e); }
                }
            });

        // 2) Daily reminder within the last 7 days before expiry
        User::whereNotNull('plan_expires_at')
            ->where('plan_expires_at', '>=', $now)
            ->where('plan_expires_at', '<=', $now->copy()->addDays(7))
            ->whereNotNull('partner_plan')
            ->where('partner_plan', '!=', 'Free')
            ->where(function ($q) {
                $q->whereNull('plan_expiry_reminded_on')
                  ->orWhereDate('plan_expiry_reminded_on', '<', now()->toDateString());
            })
            ->orderBy('id')
            ->chunkById(100, function ($owners) use ($plans, &$reminded) {
                foreach ($owners as $o) {
                    $daysLeft = max(1, (int) ceil(now()->floatDiffInDays($o->plan_expires_at, false)));
                    if ($daysLeft < 1 || $daysLeft > 7) { continue; }
                    try { $plans->sendExpiryReminder($o, $daysLeft); $reminded++; }
                    catch (\Throwable $e) { report($e); }
                }
            });

        $this->info("Plans expired/downgraded: {$downgraded}; reminders sent: {$reminded}");
        return self::SUCCESS;
    }
}
