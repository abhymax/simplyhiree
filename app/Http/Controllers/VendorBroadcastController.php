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

        // Audience size (respects any filters already chosen)
        $filters  = $this->filtersFromRequest($request);
        $audience = $this->audienceQuery($scope, $filters)->count();

        return view('vendor_broadcasts.index', [
            'broadcasts'  => $broadcasts,
            'templates'   => self::TEMPLATES,
            'audience'    => $audience,
            'scope'       => $scope,
            'filters'     => $filters,
            'tierOptions' => User::role('partner')->whereNull('parent_partner_id')
                                ->whereNotNull('partner_tier')->distinct()
                                ->orderBy('partner_tier')->pluck('partner_tier')->filter()->values(),
            'planOptions' => User::role('partner')->whereNull('parent_partner_id')
                                ->whereNotNull('partner_plan')->distinct()
                                ->orderBy('partner_plan')->pluck('partner_plan')->filter()->values(),
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

        $request->validate(self::FILTER_RULES);

        $scope = $this->resolveScope();
        $filters = $this->filtersFromRequest($request);
        $partners = $this->audienceQuery($scope, $filters)->get();

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

            // Persist progress as we go. A large audience can outrun the web
            // request timeout, and counts written only at the end would then
            // stay at 0 even though hundreds of messages had gone out.
            if ((($sent + $failed) % 25) === 0) {
                $broadcast->update(['sent_count' => $sent, 'failed_count' => $failed]);
            }
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
    /** Pull the targeting filters out of the request. */
    private function filtersFromRequest(Request $request): array
    {
        return [
            'tier'              => array_filter((array) $request->input('tier', [])),
            'plan'              => array_filter((array) $request->input('plan', [])),
            'min_rating'        => $request->input('min_rating'),
            'location'          => trim((string) $request->input('location', '')) ?: null,
            'category'          => trim((string) $request->input('category', '')) ?: null,
            'activity'          => $request->input('activity', 'any'),
            'activity_days'     => (int) $request->input('activity_days', 30) ?: 30,
            'exclude_penalised' => (bool) $request->boolean('exclude_penalised'),
            'consent_only'      => (bool) $request->boolean('consent_only'),
            'exclude_ids'       => array_filter((array) $request->input('exclude_ids', [])),
        ];
    }

    /** Live audience preview for the compose form (count + a few names). */
    public function preview(Request $request)
    {
        $request->validate(self::FILTER_RULES);
        $scope   = $this->resolveScope();
        $filters = $this->filtersFromRequest($request);
        $query   = $this->audienceQuery($scope, $filters);

        return response()->json([
            'count'  => (clone $query)->count(),
            'sample' => (clone $query)->limit(8)->get(['id', 'name', 'partner_tier', 'partner_plan'])
                            ->map(fn ($u) => [
                                'id'   => $u->id,
                                'name' => $u->name,
                                'meta' => trim(($u->partner_tier ?? '') . ' ' . ($u->partner_plan ?? '')),
                            ]),
        ]);
    }

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
    /**
     * Allowed targeting filters, surfaced in the compose form so an operator
     * can narrow the audience instead of always messaging every vendor.
     */
    public const FILTER_RULES = [
        'tier'            => 'nullable|array',
        'tier.*'          => 'string|max:40',
        'plan'            => 'nullable|array',
        'plan.*'          => 'string|max:40',
        'min_rating'      => 'nullable|numeric|min:0|max:5',
        'location'        => 'nullable|string|max:120',
        'category'        => 'nullable|string|max:120',
        'activity'        => 'nullable|in:any,active,dormant,never',
        'activity_days'   => 'nullable|integer|min:1|max:365',
        'exclude_penalised' => 'nullable|boolean',
        'consent_only'    => 'nullable|boolean',
        'exclude_ids'     => 'nullable|array',
        'exclude_ids.*'   => 'integer',
    ];

    private function audienceQuery(array $scope, array $f = [])
    {
        $base = User::role('partner')
            ->whereNull('parent_partner_id')
            ->where('status', 'active')
            ->with('profile');

        // --- targeting filters ---
        if (!empty($f['tier'])) {
            $base->whereIn('partner_tier', (array) $f['tier']);
        }
        if (!empty($f['plan'])) {
            $base->whereIn('partner_plan', (array) $f['plan']);
        }
        if (isset($f['min_rating']) && $f['min_rating'] !== null && $f['min_rating'] !== '') {
            $base->where('avg_rating', '>=', (float) $f['min_rating']);
        }
        if (!empty($f['exclude_penalised'])) {
            $base->where(fn ($q) => $q->whereNull('penalty_active')->orWhere('penalty_active', false));
        }
        if (!empty($f['consent_only'])) {
            $base->where('marketing_consent', true);
        }
        if (!empty($f['location'])) {
            $loc = $f['location'];
            $base->whereHas('profile', fn ($q) => $q->where('preferred_locations', 'like', "%{$loc}%")
                                                    ->orWhere('location', 'like', "%{$loc}%"));
        }
        if (!empty($f['category'])) {
            $cat = $f['category'];
            $base->whereHas('profile', fn ($q) => $q->where('specialization', 'like', "%{$cat}%")
                                                    ->orWhere('skills', 'like', "%{$cat}%"));
        }
        $activity = $f['activity'] ?? 'any';
        if ($activity !== 'any' && $activity !== null) {
            $days = (int) ($f['activity_days'] ?? 30);
            $since = now()->subDays($days);
            $submitted = function ($q) use ($since) {
                $q->whereHas('sourcedCandidates', fn ($c) => $c->where('created_at', '>=', $since));
            };
            if ($activity === 'active') {
                $base->where($submitted);
            } elseif ($activity === 'dormant') {
                $base->whereDoesntHave('sourcedCandidates', fn ($c) => $c->where('created_at', '>=', $since));
            } elseif ($activity === 'never') {
                $base->whereDoesntHave('sourcedCandidates');
            }
        }
        if (!empty($f['exclude_ids'])) {
            $base->whereNotIn('id', array_map('intval', (array) $f['exclude_ids']));
        }

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
