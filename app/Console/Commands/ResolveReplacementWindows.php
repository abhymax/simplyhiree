<?php

namespace App\Console\Commands;

use App\Models\JobApplication;
use App\Models\PartnerCreditNote;
use Illuminate\Console\Command;

class ResolveReplacementWindows extends Command
{
    protected $signature = 'replacements:resolve';

    protected $description = 'Auto-resolve replacement windows. If partner has not supplied a replacement by the deadline, generate a partner credit note and mark the source application as credit_pending.';

    public function handle(): int
    {
        $this->info('['.now()->toDateTimeString().'] Replacement-window sweep started');

        $count = 0;
        JobApplication::query()
            ->where('replacement_status', 'window_open')
            ->whereNotNull('replacement_deadline')
            ->where('replacement_deadline', '<', now())
            ->whereNull('replacement_application_id')
            ->with(['job', 'candidate.partner'])
            ->chunkById(100, function ($apps) use (&$count) {
                foreach ($apps as $app) {
                    $partner = $app->candidate?->partner;
                    if (!$partner) {
                        // No partner to credit — just close it.
                        $app->update(['replacement_status' => 'closed']);
                        continue;
                    }

                    $amount = (float) ($app->job?->payout_amount ?? 0);
                    if ($amount <= 0) {
                        $app->update(['replacement_status' => 'closed']);
                        $this->line("  closed #{$app->id} (no payout to credit)");
                        continue;
                    }

                    PartnerCreditNote::updateOrCreate(
                        ['source_application_id' => $app->id],
                        [
                            'partner_id' => $partner->id,
                            'amount'     => $amount,
                            'status'     => 'pending',
                            'reason'     => 'Replacement window expired without a replacement candidate.',
                        ]
                    );

                    $app->update(['replacement_status' => 'credit_pending']);
                    $count++;
                    $this->line("  credit_pending #{$app->id} partner={$partner->id} amount=₹".number_format($amount));
                }
            });

        $this->info("Replacement-window sweep done. Credit notes generated/updated: {$count}.");
        return self::SUCCESS;
    }
}
