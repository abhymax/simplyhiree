<?php

namespace App\Console\Commands;

use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
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

            $csvBody = $this->buildCsv($rows);
            $csvName = 'simplyhiree_approved_' . now()->format('Ymd') . '.csv';

            try {
                Mail::send('support.client_approved_digest', [
                    'client'       => $client,
                    'applications' => $rows,
                    'date'         => now(),
                ], function ($message) use ($client, $rows, $csvBody, $csvName) {
                    $message->to($client->email, $client->name)
                        ->subject('[SimplyHiree] ' . $rows->count() . ' new approved candidate' . ($rows->count() === 1 ? '' : 's') . ' for your jobs — ' . now()->format('d M Y'));
                    $message->attachData($csvBody, $csvName, [
                        'mime' => 'text/csv',
                    ]);
                });

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

    /**
     * Build a CSV (same field set as the admin Tracker Download) for the
     * given applications and return it as a string suitable for attachData.
     */
    public function buildCsv(Collection $applications): string
    {
        $fh = fopen('php://temp', 'w+');
        fwrite($fh, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

        fputcsv($fh, [
            'Date of Application',
            'Application Code',
            'Candidate Name',
            'Email',
            'Phone',
            'Current Location',
            'Preferred Locations',
            'Total Experience',
            'Current Company',
            'Current Designation',
            'Current Annual Salary',
            'Expected Salary',
            'Notice Period',
            'Gender',
            'Marital Status',
            'Qualification',
            'Job Title',
            'Job Code',
            'Source (Partner)',
            'Status',
        ]);

        foreach ($applications as $app) {
            $cand = $app->candidate;
            $prof = $app->candidateUser?->profile;
            $job  = $app->job;

            $name = $cand
                ? trim(($cand->first_name ?? '') . ' ' . ($cand->last_name ?? ''))
                : ($app->candidateUser?->name ?? '');

            $expY = $cand?->total_experience_years ?? $prof?->total_experience_years;
            $expM = $cand?->total_experience_months ?? $prof?->total_experience_months;
            $totalExp = ($expY === null && $expM === null)
                ? ($cand?->experience_status ?? $prof?->experience_status ?? '')
                : ((int) ($expY ?? 0)) . ' Year(s) ' . ((int) ($expM ?? 0)) . ' Month(s)';

            $prefRaw = $cand?->preferred_locations ?? $prof?->preferred_locations ?? null;
            $prefLoc = is_array($prefRaw) ? implode(', ', $prefRaw) : ($prefRaw ?: '');

            $qualLevel = $cand?->education_level ?? '';
            $qualDeg   = $cand?->qualification_degree ?? $prof?->qualification_degree ?? '';
            $spec      = $cand?->specialization ?? $prof?->specialization ?? '';
            $qualParts = array_filter([$qualDeg, $spec], fn ($v) => $v !== '' && $v !== null);
            $qual      = implode(' — ', $qualParts);
            if ($qualLevel) $qual = trim(($qual ? $qual . ' ' : '') . '(' . $qualLevel . ')');

            $partnerName = $cand?->partner?->name ?? 'Direct';
            $appCode = $app->application_code ?? ('SH-APP-' . str_pad((string) $app->id, 6, '0', STR_PAD_LEFT));
            $jobCode = $job?->job_code ?? ('SH-JOB-' . str_pad((string) ($job?->id ?? 0), 6, '0', STR_PAD_LEFT));

            fputcsv($fh, [
                optional($app->created_at)->format('Y-m-d'),
                $appCode,
                $name,
                $cand?->email ?? $app->candidateUser?->email ?? '',
                $cand?->phone ?? $prof?->phone_number ?? '',
                $cand?->current_location ?? $prof?->current_location ?? '',
                $prefLoc,
                $totalExp,
                $cand?->current_company_name ?? $prof?->current_company_name ?? '',
                $cand?->current_designation ?? $prof?->current_designation ?? '',
                $cand?->current_salary ?? $prof?->current_salary ?? '',
                $cand?->expected_salary ?? $prof?->expected_salary ?? '',
                $cand?->notice_period ?? $prof?->notice_period ?? '',
                $cand?->gender ?? $prof?->gender ?? '',
                $cand?->marital_status ?? $prof?->marital_status ?? '',
                $qual,
                $job?->title ?? '',
                $jobCode,
                $partnerName,
                $app->status ?? '',
            ]);
        }

        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);
        return $csv;
    }
}
