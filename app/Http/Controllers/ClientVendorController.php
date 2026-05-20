<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\ClientVendorInvitation;
use App\Models\ClientVendorAssignmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClientVendorController extends Controller
{
    /**
     * Browse marketplace vendors (active partners) with filters.
     */
    public function browse(Request $request)
    {
        $client = Auth::user();

        // Scope: only vendors connected to this client.
        //   1. Preferred vendors — manually picked by the client OR assigned to
        //      them by SimplyHiree (admin fulfils assignment requests by adding
        //      partners here).
        //   2. Vendors who joined via this client's own invitation flow
        //      (client_vendor_invitations with status=joined).
        $preferredIds = $client->preferredVendors()->pluck('users.id')->all();
        $invitedJoinedIds = ClientVendorInvitation::where('client_id', $client->id)
            ->where('status', 'joined')
            ->whereNotNull('joined_partner_id')
            ->pluck('joined_partner_id')
            ->all();
        $connectedIds = array_values(array_unique(array_merge($preferredIds, $invitedJoinedIds)));

        $query = User::role('partner')
            ->whereNull('parent_partner_id')
            ->where('status', 'active')
            ->whereIn('id', $connectedIds ?: [0]); // [0] forces empty result set when client has no connections

        if ($request->filled('search')) {
            $t = $request->input('search');
            $query->where(fn ($q) => $q->where('name','like',"%{$t}%")->orWhere('email','like',"%{$t}%"));
        }
        if ($request->filled('min_rating')) {
            $query->where('avg_rating', '>=', (float) $request->input('min_rating'));
        }
        if ($request->filled('level')) {
            $query->where('vendor_level', $request->input('level'));
        }
        if ($request->filled('badge')) {
            $query->where('vendor_badge', $request->input('badge'));
        }
        if ($request->filled('location')) {
            $loc = $request->input('location');
            $query->whereHas('profile', fn ($q) => $q->where('location', 'like', "%{$loc}%"));
        }
        if ($request->filled('industry')) {
            $ind = $request->input('industry');
            $query->whereHas('partnerProfile', fn ($q) => $q->where('industry', 'like', "%{$ind}%")->orWhere('company_type', 'like', "%{$ind}%"));
        }

        $vendors = $query->orderByDesc('avg_rating')->orderByDesc('total_ratings')
            ->paginate(20)->withQueryString();

        $preferredIds = $client->preferredVendors()->pluck('users.id')->all();

        return view('client.vendors.browse', compact('vendors','preferredIds'));
    }

    public function togglePreferred(Request $request, User $user)
    {
        $client = Auth::user();
        if (!$user->hasRole('partner') || !empty($user->parent_partner_id)) abort(404);

        $existing = $client->preferredVendors()->where('partner_id', $user->id)->exists();
        if ($existing) {
            $client->preferredVendors()->detach($user->id);
            $msg = "Removed {$user->name} from your preferred list.";
        } else {
            $client->preferredVendors()->attach($user->id, ['added_at' => now()]);
            $msg = "Added {$user->name} to your preferred list.";
        }
        return back()->with('success', $msg);
    }

    public function invitePage()
    {
        $client = Auth::user();
        $invitations = ClientVendorInvitation::where('client_id', $client->id)->latest()->paginate(15);
        return view('client.vendors.invite', compact('invitations'));
    }

    public function inviteStore(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
        ]);
        if (empty($data['email']) && empty($data['phone'])) {
            return back()->with('error', 'Provide at least an email or a phone number for the invite.');
        }

        ClientVendorInvitation::create(array_merge($data, [
            'client_id'    => Auth::id(),
            'invite_token' => Str::random(40),
            'status'       => 'pending',
        ]));

        return back()->with('success', "Invitation logged. Share the link with {$data['name']} from your invitations list.");
    }

    public function requestAssignmentPage()
    {
        $client = Auth::user();
        $requests = ClientVendorAssignmentRequest::where('client_id', $client->id)->latest()->paginate(10);
        return view('client.vendors.request-assignment', compact('requests'));
    }

    public function requestAssignmentStore(Request $request)
    {
        $data = $request->validate([
            'vendor_count'  => 'required|integer|min:1|max:50',
            'industry_hint' => 'nullable|string|max:255',
            'location_hint' => 'nullable|string|max:255',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $client  = Auth::user();
        $reqRow = ClientVendorAssignmentRequest::create(array_merge($data, [
            'client_id' => $client->id,
            'status'    => 'pending',
        ]));

        // Notify every active Superadmin via email so they pick this up quickly.
        try {
            $admins = \App\Models\User::role(['Superadmin', 'Manager'])
                ->where('status', 'active')
                ->whereNotNull('email')
                ->get();

            foreach ($admins as $admin) {
                \Illuminate\Support\Facades\Mail::send('admin.vendor_assignment_requests.email_notification', [
                    'admin'   => $admin,
                    'client'  => $client,
                    'request' => $reqRow,
                ], function ($m) use ($admin, $client, $reqRow) {
                    $m->to($admin->email, $admin->name)
                      ->subject('[SimplyHiree] New vendor assignment request from ' . $client->name);
                });
            }
        } catch (\Throwable $e) {
            \Log::warning('Vendor assignment request notify failed: ' . $e->getMessage());
        }

        return back()->with('success', "Request submitted. The SimplyHiree team will assign the top {$data['vendor_count']} vendors shortly.");
    }

    /**
     * Vendor performance dashboard for this client only.
     * Scoped to vendors that are connected to this client — either
     * invited by the client themselves OR assigned by SimplyHiree.
     */
    public function performance(Request $request)
    {
        $client = Auth::user();
        $myJobIds = Job::where('user_id', $client->id)->pluck('id');

        // Same scoping as the My Vendors page:
        //   preferred (client-picked OR admin-assigned) + invitation-joined
        $preferredIds = $client->preferredVendors()->pluck('users.id')->all();
        $invitedJoinedIds = ClientVendorInvitation::where('client_id', $client->id)
            ->where('status', 'joined')
            ->whereNotNull('joined_partner_id')
            ->pluck('joined_partner_id')
            ->all();
        $connectedIds = array_values(array_unique(array_merge($preferredIds, $invitedJoinedIds)));

        // Per-partner aggregates: limit to BOTH the client's jobs AND vendors
        // the client is officially connected to. Empty connection list means
        // empty performance dashboard, which is the correct behavior.
        $rows = JobApplication::query()
            ->whereIn('job_id', $myJobIds)
            ->whereHas('candidate', fn ($q) => $q->whereNotNull('partner_id')->whereIn('partner_id', $connectedIds ?: [0]))
            ->join('candidates', 'candidates.id', '=', 'job_applications.candidate_id')
            ->join('users as p', 'p.id', '=', 'candidates.partner_id')
            ->join('jobs', 'jobs.id', '=', 'job_applications.job_id')
            ->whereIn('p.id', $connectedIds ?: [0])
            ->selectRaw("
                p.id as partner_id, p.name as partner_name, p.avg_rating, p.vendor_level, p.vendor_badge,
                COUNT(job_applications.id) as submitted,
                SUM(CASE WHEN job_applications.hiring_status = 'Selected' THEN 1 ELSE 0 END) as selected,
                SUM(CASE WHEN job_applications.joined_status = 'Joined' THEN 1 ELSE 0 END) as joined_count,
                SUM(CASE WHEN job_applications.joined_status = 'Left' THEN 1 ELSE 0 END) as left_count,
                SUM(CASE WHEN job_applications.joined_status = 'Joined' THEN jobs.payout_amount ELSE 0 END) as revenue
            ")
            ->groupBy('p.id','p.name','p.avg_rating','p.vendor_level','p.vendor_badge')
            ->orderByDesc('joined_count')
            ->get();

        // Rank: Top / Average / Low
        $maxJoined = (int) ($rows->max('joined_count') ?: 0);
        $rankBucket = function ($r) use ($maxJoined) {
            $ratio = $r->submitted > 0 ? $r->selected / max(1, $r->submitted) : 0;
            $score = ($ratio * 60) + (min(1, ($r->joined_count / max(1, $maxJoined))) * 40);
            if ($score >= 65) return 'Top';
            if ($score >= 35) return 'Average';
            return 'Low';
        };
        foreach ($rows as $r) {
            $r->rank      = $rankBucket($r);
            $r->sel_ratio = $r->submitted > 0 ? round($r->selected / $r->submitted * 100, 1) : 0;
            $r->drop_rate = $r->joined_count > 0 ? round($r->left_count / $r->joined_count * 100, 1) : 0;
        }

        // Auto-suggestions
        $suggestions = [];
        foreach ($rows as $r) {
            if ($r->rank === 'Top' && $r->joined_count >= 2) {
                $suggestions[] = ['type'=>'good','text'=>"Vendor {$r->partner_name} is a top performer. Give them more jobs."];
            }
            if ($r->rank === 'Low' && $r->submitted >= 5) {
                $suggestions[] = ['type'=>'warn','text'=>"Vendor {$r->partner_name} is underperforming ({$r->sel_ratio}% selection). Consider replacing."];
            }
            if ($r->drop_rate >= 40 && $r->joined_count >= 2) {
                $suggestions[] = ['type'=>'warn','text'=>"Vendor {$r->partner_name} has a high drop rate ({$r->drop_rate}%)."];
            }
        }

        return view('client.vendors.performance', compact('rows','suggestions'));
    }
}
