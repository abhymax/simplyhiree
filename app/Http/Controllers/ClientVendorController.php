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
        $query = User::role('partner')
            ->whereNull('parent_partner_id')
            ->where('status', 'active');

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

        ClientVendorAssignmentRequest::create(array_merge($data, [
            'client_id' => Auth::id(),
            'status'    => 'pending',
        ]));

        return back()->with('success', "Request submitted. The SimplyHiree team will assign the top {$data['vendor_count']} vendors shortly.");
    }

    /**
     * Vendor performance dashboard for this client only.
     */
    public function performance(Request $request)
    {
        $client = Auth::user();
        $myJobIds = Job::where('user_id', $client->id)->pluck('id');

        // Per-partner aggregates limited to this client's jobs
        $rows = JobApplication::query()
            ->whereIn('job_id', $myJobIds)
            ->whereHas('candidate', fn ($q) => $q->whereNotNull('partner_id'))
            ->join('candidates', 'candidates.id', '=', 'job_applications.candidate_id')
            ->join('users as p', 'p.id', '=', 'candidates.partner_id')
            ->join('jobs', 'jobs.id', '=', 'job_applications.job_id')
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
