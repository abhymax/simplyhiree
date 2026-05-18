<?php

namespace App\Console\Commands;

use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendClientApprovedDigest extends Command
{
    protected $signature = 'clients:approved-applications-digest
        {--client= : Restrict to a single client user id}
        {--dry : Show counts without sending emails or marking rows}
        {--resend : Include applications already in a previous digest}';

    protected $description = 'Email each client a branded morning digest of applications approved by superadmin since the last run';

    public function handle(): int
    {
        $clientId  = $this->option('client');
        $dry       = (bool) $this->option('dry');
        $resend    = (bool) $this->option('resend');

        $query = JobApplication::query()
            ->with(['job', 'candidate.partner', 'candidateUser.profile'])
            ->where('status', 'Approved')
            ->whereHas('job', function ($q) use ($clientId) {
                $q->whereNotNull('user_id');
                if ($clientId) $q->where('user_id', (int) $clientId);
            });

        if (!$resend) {
            $query->whereNull('approved_digest_sent_at');
        }

        // Group by client (job owner)
        $apps = $query->get()->groupBy(fn ($a) => $a->job?->user_id);

        if ($apps->isEmpty()) {
            $this->info('No approved applications pending a digest.');
            return self::SUCCESS;
        }

        $sentClients = 0;
        $sentRows    = 0;
        $errors      = 0;

        foreach ($apps as $clientUserId => $rows) {
            if (!$clientUserId) continue;
            $client = User::find($clientUserId);
            if (!$client || !$client->email) continue;

            $this->info("Client #{$clientUserId} ({$client->email}) — {$rows->count()} applications");
            if ($dry) continue;

            try {
                Mail::send('support.client_approved_digest', [
                    'client'       => $client,
                    'applications' => $rows,
                    'date'         => now(),
                ], function ($message) use ($client, $rows) {
                    $message->to($client->email, $client->name)
                        ->subject('[SimplyHiree] ' . $rows->count() . ' new approved candidate' . ($rows->count() === 1 ? '' : 's') . ' for your jobs — ' . now()->format('d M Y'));
                });

                // Mark them as included in a digest
                JobApplication::whereIn('id', $rows->pluck('id'))->update([
                    'approved_digest_sent_at' => now(),
                ]);

                $sentClients++;
                $sentRows += $rows->count();
            } catch (\Throwable $e) {
                $errors++;
                $this->error("Failed for client #{$clientUserId}: " . $e->getMessage());
            }
        }

        $this->info("Done. Sent {$sentClients} client emails covering {$sentRows} applications. Errors: {$errors}.");
        return self::SUCCESS;
    }
}
