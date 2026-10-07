<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\VendorBroadcast;
use App\Models\VendorBroadcastRecipient;
use App\Services\AiSensyWhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Deliver ONE vendor broadcast message. Sending ran inside the web request
 * before, which outran the request timeout on large audiences and left most
 * vendors uncontacted. One job per recipient keeps each unit small and
 * retryable.
 *
 * Runs on its own "broadcasts" queue so it is unaffected by any backlog
 * sitting on the default queue.
 */
class SendVendorBroadcastMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;
    public int $timeout = 60;

    public function __construct(
        public int $broadcastId,
        public int $partnerId,
        public bool $useWhatsapp,
        public bool $useEmail,
    ) {
        $this->onQueue('broadcasts');
    }

    public function handle(AiSensyWhatsAppService $whatsapp): void
    {
        $broadcast = VendorBroadcast::find($this->broadcastId);
        $partner   = User::with('profile', 'partnerProfile')->find($this->partnerId);

        if (!$broadcast || !$partner) {
            return;
        }

        // Already delivered (e.g. a retry after a partial failure).
        $existing = VendorBroadcastRecipient::where('broadcast_id', $broadcast->id)
            ->where('partner_id', $partner->id)->first();
        if ($existing && $existing->delivered_at) {
            return;
        }

        // Talk to the local Exim listener over SMTP: the sendmail binary hits
        // permission errors under PHP-FPM when called in a tight loop.
        config(['mail.mailers.broadcast_smtp' => [
            'transport'    => 'smtp',
            'host'         => '127.0.0.1',
            'port'         => 25,
            'encryption'   => null,
            'username'     => null,
            'password'     => null,
            'timeout'      => 10,
            'local_domain' => 'simplyhiree.com',
            'verify_peer'  => false,
        ]]);

        $waStatus = $emStatus = null;
        $err = null;

        if ($this->useWhatsapp) {
            // Vendors keep their number on either profile, so check both.
            $phone = optional($partner->profile)->phone_number
                ?: optional($partner->partnerProfile)->contact_phone
                ?: ($partner->phone ?? null);

            if (!$phone) {
                $waStatus = 'skipped';
                $err = 'no_phone';
            } else {
                try {
                    $res = $whatsapp->sendEventAlert(
                        $phone,
                        'vendor_broadcast',
                        $broadcast->subject,
                        $broadcast->body,
                        ['template_params' => [
                            $partner->name ?? 'Partner',
                            $broadcast->subject,
                            mb_strimwidth($broadcast->body, 0, 280, '…'),
                        ]]
                    );
                    $waStatus = ($res['ok'] ?? false) ? 'sent' : 'failed';
                    if (!($res['ok'] ?? false)) {
                        $err = $res['error'] ?? 'whatsapp_failed';
                    }
                } catch (\Throwable $e) {
                    $waStatus = 'failed';
                    $err = $e->getMessage();
                }
            }
        }

        if ($this->useEmail) {
            if (!$partner->email) {
                $emStatus = 'skipped';
                $err = ($err ? $err . ' | ' : '') . 'no_email';
            } else {
                try {
                    Mail::mailer('broadcast_smtp')->raw($broadcast->body, function ($m) use ($partner, $broadcast) {
                        $m->to($partner->email, $partner->name ?: null)
                          ->subject($broadcast->subject);
                    });
                    $emStatus = 'sent';
                } catch (\Throwable $e) {
                    $emStatus = 'failed';
                    $err = ($err ? $err . ' | ' : '') . 'email: ' . $e->getMessage();
                    Log::warning('Vendor broadcast email failed', [
                        'partner_id'   => $partner->id,
                        'broadcast_id' => $broadcast->id,
                        'err'          => $e->getMessage(),
                    ]);
                }
            }
        }

        $succeeded = ($waStatus === 'sent') || ($emStatus === 'sent');

        VendorBroadcastRecipient::updateOrCreate(
            ['broadcast_id' => $broadcast->id, 'partner_id' => $partner->id],
            [
                'whatsapp_status' => $waStatus,
                'email_status'    => $emStatus,
                'error'           => $err,
                'delivered_at'    => $succeeded ? now() : null,
            ]
        );

        // Recount from the recipient rows so retries can't double-count.
        $sent = VendorBroadcastRecipient::where('broadcast_id', $broadcast->id)
            ->whereNotNull('delivered_at')->count();
        $failed = VendorBroadcastRecipient::where('broadcast_id', $broadcast->id)
            ->whereNull('delivered_at')->count();

        DB::table('vendor_broadcasts')->where('id', $broadcast->id)
            ->update(['sent_count' => $sent, 'failed_count' => $failed]);
    }
}
