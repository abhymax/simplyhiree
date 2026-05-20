<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VendorBroadcast;
use App\Models\VendorBroadcastRecipient;
use App\Services\AiSensyWhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VendorBroadcastController extends Controller
{
    /**
     * Pre-built templates so admin / client can pick instead of writing
     * from scratch. {placeholders} are not auto-substituted — the operator
     * edits the text inline before sending.
     */
    public const TEMPLATES = [
        'urgent_hiring' => [
            'subject' => 'Urgent Hiring — Multiple positions open',
            'body'    => "Hi team,\n\nWe have an URGENT requirement for 20+ positions across multiple roles. Please submit your best candidates ASAP — first 48 hours are critical.\n\nReply on WhatsApp / email if you want the full JD list.\n\n— SimplyHiree",
        ],
        'salary_update' => [
            'subject' => 'Updated salary bands — please re-submit',
            'body'    => "Hi team,\n\nSalary ranges for our open positions have been revised upward. Please review the current postings on your dashboard and re-share with candidates who were previously borderline.\n\n— SimplyHiree",
        ],
        'new_jobs' => [
            'subject' => 'New jobs posted — start submitting',
            'body'    => "Hi team,\n\nFresh requirements have just gone live on the platform. Log in to your dashboard and submit candidates today to maximize your chances of selection.\n\n— SimplyHiree",
        ],
        'gentle_nudge' => [
            'subject' => 'Quick reminder — submissions awaited',
            'body'    => "Hi team,\n\nFriendly nudge — we have open positions with low submission volume. Even one quality candidate from you helps. Please check your dashboard.\n\n— SimplyHiree",
        ],
    ];

    public function index(Request $request)
    {
        $scope = $this->resolveScope();

        $broadcasts = VendorBroadcast::with('sender')
            ->when($scope['type'] === 'client', fn ($q) => $q->where('sender_id', Auth::id()))
            ->latest()
            ->paginate(20);

        // Audience size
        $audience = $this->audienceQuery($scope)->count();

        return view('vendor_broadcasts.index', [
            'broadcasts' => $broadcasts,
            'templates'  => self::TEMPLATES,
            'audience'   => $audience,
            'scope'      => $scope,
        ]);
    }

    public function store(Request $request, AiSensyWhatsAppService $whatsapp)
    {
        $validated = $request->validate([
            'subject'      => 'required|string|max:200',
            'body'         => 'required|string|max:5000',
            'template_key' => 'nullable|string|max:60',
            'channels'     => 'required|array|min:1',
            'channels.*'   => 'in:whatsapp,email',
        ]);

        $scope = $this->resolveScope();
        $partners = $this->audienceQuery($scope)->get();

        if ($partners->isEmpty()) {
            return back()->withInput()->with('error', 'No partners in your audience right now. Nothing was sent.');
        }

        $channels = implode(',', $validated['channels']);

        $broadcast = VendorBroadcast::create([
            'sender_id'       => Auth::id(),
            'sender_role'     => $scope['type'],
            'subject'         => $validated['subject'],
            'body'            => $validated['body'],
            'template_key'    => $validated['template_key'] ?? 'custom',
            'channels'        => $channels,
            'recipient_count' => $partners->count(),
            'sent_count'      => 0,
            'failed_count'    => 0,
            'dispatched_at'   => now(),
        ]);

        $useWhatsapp = in_array('whatsapp', $validated['channels'], true);
        $useEmail    = in_array('email', $validated['channels'], true);

        // Register a one-off "broadcast_smtp" mailer that talks to the local
        // Exim listener directly. The sendmail binary path hits
        // "Cannot open /var/log/exim_mainlog: Permission denied" under PHP-FPM
        // when called in a tight loop, so we bypass it and use SMTP/127.0.0.1.
        config(['mail.mailers.broadcast_smtp' => [
            'transport'   => 'smtp',
            'host'        => '127.0.0.1',
            'port'        => 25,
            'encryption'  => null,
            'username'    => null,
            'password'    => null,
            'timeout'     => 10,
            'local_domain' => 'simplyhiree.com',
            'verify_peer' => false,
        ]]);

        $sent = 0;
        $failed = 0;

        foreach ($partners as $p) {
            $waStatus = $emStatus = null;
            $err = null;

            // --- WhatsApp ---
            if ($useWhatsapp) {
                $phone = optional($p->profile)->phone_number ?: $p->phone ?? null;
                if (!$phone) {
                    $waStatus = 'skipped';
                    $err = 'no_phone';
                } else {
                    try {
                        $res = $whatsapp->sendEventAlert(
                            $phone,
                            'vendor_broadcast',
                            $validated['subject'],
                            $validated['body'],
                            ['template_params' => [
                                $p->name ?? 'Partner',
                                $validated['subject'],
                                mb_strimwidth($validated['body'], 0, 280, '…'),
                            ]]
                        );
                        $waStatus = ($res['ok'] ?? false) ? 'sent' : 'failed';
                        if (!($res['ok'] ?? false)) $err = $res['error'] ?? 'whatsapp_failed';
                    } catch (\Throwable $e) {
                        $waStatus = 'failed';
                        $err = $e->getMessage();
                    }
                }
            }

            // --- Email ---
            if ($useEmail && !empty($p->email)) {
                try {
                    Mail::mailer('broadcast_smtp')->send('vendor_broadcasts.email', [
                        'partner'   => $p,
                        'subject'   => $validated['subject'],
                        'body'      => $validated['body'],
                        'broadcast' => $broadcast,
                    ], function ($message) use ($p, $validated) {
                        $message->to($p->email, $p->name)
                            ->subject('[SimplyHiree] ' . $validated['subject']);
                    });
                    $emStatus = 'sent';
                } catch (\Throwable $e) {
                    $emStatus = 'failed';
                    $err = ($err ? $err . ' | ' : '') . 'email: ' . $e->getMessage();
                    Log::warning('Vendor broadcast email failed', ['partner_id' => $p->id, 'broadcast_id' => $broadcast->id, 'err' => $e->getMessage()]);
                }
            }

            // Pace the loop so Exim isn't hammered (the sendmail binary
            // would crash with permission errors under PHP-FPM otherwise).
            // 80ms × 50 partners ≈ 4 sec — well within request timeout.
            usleep(80000);

            $rowSucceeded = ($waStatus === 'sent') || ($emStatus === 'sent');
            $rowSucceeded ? $sent++ : $failed++;

            VendorBroadcastRecipient::create([
                'broadcast_id'   => $broadcast->id,
                'partner_id'     => $p->id,
                'whatsapp_status' => $waStatus,
                'email_status'   => $emStatus,
                'error'          => $err,
                'delivered_at'   => $rowSucceeded ? now() : null,
            ]);
        }

        $broadcast->update(['sent_count' => $sent, 'failed_count' => $failed]);

        $msg = "Broadcast sent to {$sent} of {$partners->count()} partners.";
        if ($failed > 0) $msg .= " ({$failed} failed — see history)";

        return redirect()->route($scope['route'])->with('success', $msg);
    }

    /**
     * Re-attempt delivery for recipients that previously failed.
     * Skips partners that already succeeded via either channel.
     */
    public function retryFailed(VendorBroadcast $broadcast, AiSensyWhatsAppService $whatsapp)
    {
        $scope = $this->resolveScope();
        if ($scope['type'] === 'client' && (int) $broadcast->sender_id !== (int) Auth::id()) abort(403);

        // Same one-off broadcast_smtp mailer config
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

        $failures = $broadcast->recipients()
            ->whereNull('delivered_at')
            ->with('partner')
            ->get();

        if ($failures->isEmpty()) {
            return back()->with('success', 'Nothing to retry — all recipients already delivered.');
        }

        $channels    = $broadcast->channelList();
        $useWhatsapp = in_array('whatsapp', $channels, true);
        $useEmail    = in_array('email', $channels, true);

        $newlySent = 0;
        foreach ($failures as $rec) {
            $p = $rec->partner;
            if (!$p) continue;

            $waStatus = $rec->whatsapp_status;
            $emStatus = $rec->email_status;
            $err = null;

            if ($useWhatsapp && $waStatus !== 'sent') {
                $phone = optional($p->profile)->phone_number ?: $p->phone ?? null;
                if ($phone) {
                    try {
                        $res = $whatsapp->sendEventAlert($phone, 'vendor_broadcast', $broadcast->subject, $broadcast->body,
                            ['template_params' => [$p->name ?? 'Partner', $broadcast->subject, mb_strimwidth($broadcast->body, 0, 280, '…')]]);
                        $waStatus = ($res['ok'] ?? false) ? 'sent' : 'failed';
                        if (!($res['ok'] ?? false)) $err = $res['error'] ?? 'whatsapp_failed';
                    } catch (\Throwable $e) {
                        $waStatus = 'failed';
                        $err = $e->getMessage();
                    }
                } else {
                    $waStatus = 'skipped';
                    $err = 'no_phone';
                }
            }

            if ($useEmail && $emStatus !== 'sent' && !empty($p->email)) {
                try {
                    Mail::mailer('broadcast_smtp')->send('vendor_broadcasts.email', [
                        'partner' => $p, 'subject' => $broadcast->subject, 'body' => $broadcast->body, 'broadcast' => $broadcast,
                    ], function ($m) use ($p, $broadcast) {
                        $m->to($p->email, $p->name)->subject('[SimplyHiree] ' . $broadcast->subject);
                    });
                    $emStatus = 'sent';
                } catch (\Throwable $e) {
                    $emStatus = 'failed';
                    $err = ($err ? $err . ' | ' : '') . 'email: ' . $e->getMessage();
                }
            }

            $rowOk = ($waStatus === 'sent') || ($emStatus === 'sent');
            $rec->update([
                'whatsapp_status' => $waStatus,
                'email_status'    => $emStatus,
                'error'           => $rowOk ? null : $err,
                'delivered_at'    => $rowOk ? now() : null,
            ]);
            if ($rowOk) $newlySent++;
            usleep(80000);
        }

        // Recompute counts
        $broadcast->update([
            'sent_count'   => $broadcast->recipients()->whereNotNull('delivered_at')->count(),
            'failed_count' => $broadcast->recipients()->whereNull('delivered_at')->count(),
        ]);

        return back()->with('success', "Retry complete. {$newlySent} of {$failures->count()} previously-failed recipients now delivered.");
    }

    public function show(VendorBroadcast $broadcast)
    {
        $scope = $this->resolveScope();
        // Clients can only see their own broadcasts
        if ($scope['type'] === 'client' && (int) $broadcast->sender_id !== (int) Auth::id()) {
            abort(403);
        }
        $broadcast->load(['sender', 'recipients.partner']);
        return view('vendor_broadcasts.show', compact('broadcast'));
    }

    /**
     * Resolve who's calling — admin or client — and what audience they
     * can reach.
     */
    private function resolveScope(): array
    {
        $u = Auth::user();
        if ($u->hasRole('Superadmin') || $u->hasRole('Manager')) {
            return [
                'type'  => 'admin',
                'route' => 'admin.broadcasts.index',
            ];
        }
        return [
            'type'  => 'client',
            'route' => 'client.broadcasts.index',
        ];
    }

    /**
     * Build the audience query: admin = all active partner-owners;
     * client = vendors connected to that client (preferred + invited-joined).
     */
    private function audienceQuery(array $scope)
    {
        $base = User::role('partner')
            ->whereNull('parent_partner_id')
            ->where('status', 'active')
            ->with('profile');

        if ($scope['type'] === 'client') {
            $client = Auth::user();
            $preferredIds = $client->preferredVendors()->pluck('users.id')->all();
            $invitedJoinedIds = \App\Models\ClientVendorInvitation::where('client_id', $client->id)
                ->where('status', 'joined')->whereNotNull('joined_partner_id')
                ->pluck('joined_partner_id')->all();
            $connected = array_values(array_unique(array_merge($preferredIds, $invitedJoinedIds)));
            $base->whereIn('id', $connected ?: [0]);
        }
        return $base;
    }
}
