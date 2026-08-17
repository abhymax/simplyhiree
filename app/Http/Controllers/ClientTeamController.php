<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ClientTeamController extends Controller
{
    /** Grantable module keys shown as checkboxes when adding a member. */
    public const MODULES = [
        'job_posting' => 'Job Posting',
        'applicants'  => 'Applicants & Review',
        'interviews'  => 'Interviews',
        'selection'   => 'Selection & Joining',
        'vendors'     => 'Vendors',
    ];

    private function requireOwner(): User
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('client') || !empty($user->parent_partner_id)) {
            abort(403, 'Only the client account owner can manage the team.');
        }
        return $user;
    }

    public function index()
    {
        $owner = $this->requireOwner();

        $members = User::where('parent_partner_id', $owner->id)
            ->orderBy('name')
            ->get();

        return view('client.team.index', [
            'owner'   => $owner,
            'members' => $members,
            'modules' => self::MODULES,
        ]);
    }

    public function store(Request $request)
    {
        $owner = $this->requireOwner();

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'team_name'   => 'required|string|max:255',
            'email'       => 'required|email|max:255|unique:users,email',
            'mobile'      => 'required|string|max:20',
            'password'    => 'required|string|min:8',
            'modules'     => 'nullable|array',
            'modules.*'   => ['string', Rule::in(array_keys(self::MODULES))],
        ]);

        $member = User::create([
            'name'              => $data['name'],
            'team_name'         => $data['team_name'],
            'email'             => $data['email'],
            'password'          => $data['password'], // hashed by cast
            'parent_partner_id' => $owner->id,
            'team_modules'      => array_values($data['modules'] ?? []),
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);
        $member->assignRole('client');

        $member->profile()->updateOrCreate(
            ['user_id' => $member->id],
            ['phone_number' => $data['mobile']]
        );

        return back()->with('success', 'Team member added. They can log in with their email and the password you set.');
    }

    public function update(Request $request, User $user)
    {
        $owner = $this->requireOwner();
        if ((int) $user->parent_partner_id !== (int) $owner->id) {
            abort(403);
        }

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'team_name'   => 'required|string|max:255',
            'email'       => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'mobile'      => 'required|string|max:20',
            'password'    => 'nullable|string|min:8',
            'modules'     => 'nullable|array',
            'modules.*'   => ['string', Rule::in(array_keys(self::MODULES))],
        ]);

        $update = [
            'name'         => $data['name'],
            'team_name'    => $data['team_name'],
            'email'        => $data['email'],
            'team_modules' => array_values($data['modules'] ?? []),
        ];
        if (!empty($data['password'])) {
            $update['password'] = $data['password']; // hashed by cast
        }
        $user->update($update);

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            ['phone_number' => $data['mobile']]
        );

        return back()->with('success', 'Team member updated.');
    }

    public function toggle(User $user)
    {
        $owner = $this->requireOwner();
        if ((int) $user->parent_partner_id !== (int) $owner->id) {
            abort(403);
        }
        $newStatus = $user->status === 'active' ? 'on_hold' : 'active';
        $user->update(['status' => $newStatus]);
        return back()->with('success', 'Team member ' . ($newStatus === 'active' ? 'activated' : 'suspended') . '.');
    }

    public function destroy(User $user)
    {
        $owner = $this->requireOwner();
        if ((int) $user->parent_partner_id !== (int) $owner->id) {
            abort(403);
        }
        $user->update(['status' => 'archived']);
        return back()->with('success', 'Team member archived. Their login is disabled.');
    }
}
