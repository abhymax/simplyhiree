<?php

namespace App\Http\Controllers;

use App\Models\PartnerReactivationRequest;
use App\Models\User;
use Illuminate\Http\Request;

class PartnerReactivationController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $heldId = (int) $request->session()->get('hold_user_id', 0);
        $user = User::role('partner')->whereKey($heldId)->where('email', $data['email'])->first();

        if (!$user || !in_array($user->status, ['on_hold', 'inactive'], true)) {
            return response()->json(['message' => 'This reactivation session is no longer valid. Please log in again.'], 422);
        }

        $owner = $user->parent_partner_id ? User::find($user->parent_partner_id) : $user;
        if (!$owner) {
            return response()->json(['message' => 'Partner account could not be found.'], 404);
        }

        $reactivation = PartnerReactivationRequest::firstOrNew([
            'user_id' => $owner->id,
            'status' => 'pending',
        ]);
        $reactivation->fill([
            'message' => $data['message'] ?? null,
            'requested_at' => now(),
        ])->save();

        $request->session()->forget(['hold_user_id', 'hold_email', 'hold_name']);

        return response()->json([
            'message' => $reactivation->wasRecentlyCreated
                ? 'Reactivation request sent to Superadmin.'
                : 'Your pending request has been refreshed for Superadmin review.',
        ]);
    }
}
