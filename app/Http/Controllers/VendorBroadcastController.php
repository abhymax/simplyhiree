<?php

namespace App\Http\Controllers;

use App\Jobs\SendVendorBroadcastMessage;
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

        // Hand each recipient to the queue. Sending inline could not finish a
        // large audience inside the web request, so most vendors were silently
        // never contacted. Jobs go on a dedicated "broadcasts" queue.
        foreach ($partners as $p) {
            SendVendorBroadcastMessage::dispatch($broadcast->id, $p->id, $useWhatsapp, $useEmail);
        }

        $msg = "Broadcast queued for {$partners->count()} vendors. Delivery runs in the background — "
             . "watch the history below for live sent / failed counts.";

        return redirect()->route($scope['route'])->with('success', $msg);
    }

    /**
     * Re-attempt delivery for recipients that previously failed.
     * Skips partners that already succeeded via either channel.
     */
    public function retryFailed(VendorBroadcast $broadcast)
    {
        $scope = $this->resolveScope();
        if ($scope['type'] === 'client' && (int) $broadcast->sender_id !== (int) Auth::id()) abort(403);

        $failures = $broadcast->recipients()->whereNull('delivered_at')->get();

        if ($failures->isEmpty()) {
            return back()->with('success', 'Nothing to retry — all recipients already delivered.');
        }

        $channels    = $broadcast->channelList();
        $useWhatsapp = in_array('whatsapp', $channels, true);
        $useEmail    = in_array('email', $channels, true);

        foreach ($failures as $rec) {
            if (!$rec->partner_id) continue;
            SendVendorBroadcastMessage::dispatch($broadcast->id, (int) $rec->partner_id, $useWhatsapp, $useEmail);
        }

        return back()->with('success', 'Re-queued ' . $failures->count() . ' recipient(s). Delivery runs in the background.');
    }

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
