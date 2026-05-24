<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Candidate;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PartnerTeamController extends Controller
{
    private function requireOwner(): User
    {
        $user = Auth::user();
        if (!$user->isPartnerOwner()) {
            abort(403, 'Only the partner-account owner can manage team members.');
        }
        return $user;
    }

    public function index()
    {
        $owner = Auth::user();
        if (!$owner->isPartnerOwner() && !$owner->hasRole('partner')) {
            abort(403);
        }
        $ownerId = $owner->partnerOwnerId();

        $members = User::where('parent_partner_id', $ownerId)
            ->orderBy('name')
            ->get();

        // Per-member performance metrics.
        // submitted_by_user_id is canonical for new submissions; for older
        // rows we fall back to attributing them to the team owner.
        $memberIds = $members->pluck('id')->push($ownerId)->unique()->all();

        $stats = JobApplication::query()
            ->whereIn('submitted_by_user_id', $memberIds)
            ->selectRaw("
                submitted_by_user_id,
                COUNT(*) as submitted,
                SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as shortlisted,
                SUM(CASE WHEN hiring_status IN ('Interviewed','No-Show','Interview Scheduled','Selected') OR joined_status IN ('Joined','Left','Did Not Join') THEN 1 ELSE 0 END) as interviewed,
                SUM(CASE WHEN joined_status = 'Joined' THEN 1 ELSE 0 END) as joined
            ")
            ->groupBy('submitted_by_user_id')
            ->get()
            ->keyBy('submitted_by_user_id');

        // Revenue (partner payout) per member = SUM(payout_amount) of joined hires they submitted
        $revenue = JobApplication::query()
            ->whereIn('submitted_by_user_id', $memberIds)
            ->where('joined_status', 'Joined')
            ->join('jobs', 'jobs.id', '=', 'job_applications.job_id')
            ->selectRaw('submitted_by_user_id, SUM(jobs.payout_amount) as revenue')
            ->groupBy('submitted_by_user_id')
            ->pluck('revenue', 'submitted_by_user_id');

        return view('partner.team.index', [
            'owner'   => $owner,
            'members' => $members,
            'stats'   => $stats,
            'revenue' => $revenue,
        ]);
    }

    public function store(Request $request)
    {
        $owner = $this->requireOwner();
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|max:255|unique:users,email',
            'mobile'       => 'nullable|string|max:20',
            'team_role'    => 'required|in:Manager,Recruiter',
            'access_level' => 'required|in:full,submissions_only,view_only',
            'password'     => 'required|string|min:8',
        ]);

        $member = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
            'parent_partner_id' => $owner->id,
            'team_role'         => $data['team_role'],
            'access_level'      => $data['access_level'],
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);
        $member->assignRole('partner');

        // Mirror mobile onto user_profiles so existing UIs that read it still work.
        if (!empty($data['mobile'])) {
            $member->profile()->updateOrCreate(
                ['user_id' => $member->id],
                ['phone_number' => $data['mobile']]
            );
        }

        return back()->with('success', "Team member added. They can log in with their email and the password you set.");
    }

    public function update(Request $request, User $user)
    {
        $owner = $this->requireOwner();
        if ((int) $user->parent_partner_id !== (int) $owner->id) abort(403);

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'mobile'       => 'nullable|string|max:20',
            'team_role'    => 'required|in:Manager,Recruiter',
            'access_level' => 'required|in:full,submissions_only,view_only',
            'password'     => 'nullable|string|min:8',
        ]);

        $update = [
            'name'         => $data['name'],
            'email'        => $data['email'],
            'team_role'    => $data['team_role'],
            'access_level' => $data['access_level'],
        ];
        if (!empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }
        $user->update($update);

        if (array_key_exists('mobile', $data)) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                ['phone_number' => $data['mobile']]
            );
        }

        return back()->with('success', 'Team member updated.');
    }

    public function toggle(User $user)
    {
        $owner = $this->requireOwner();
        if ((int) $user->parent_partner_id !== (int) $owner->id) abort(403);

        $newStatus = $user->status === 'active' ? 'restricted' : 'active';
        $user->update(['status' => $newStatus]);
        $label = $newStatus === 'active' ? 'enabled' : 'disabled';
        return back()->with('success', "{$user->name} has been {$label}.");
    }

    public function destroy(User $user)
    {
        $owner = $this->requireOwner();
        if ((int) $user->parent_partner_id !== (int) $owner->id) abort(403);

        // Archive: blocks login, preserves all historical submission data
        $user->update(['status' => 'archived']);
        return back()->with('success', "{$user->name} has been archived. Their login is now disabled and their submission history is preserved.");
    }
}
