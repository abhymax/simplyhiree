<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartnerReactivationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartnerReactivationController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->query('status'), ['pending', 'approved', 'rejected'], true)
            ? $request->query('status') : 'pending';

        $requests = PartnerReactivationRequest::with(['user.partnerProfile', 'reviewer'])
            ->where('status', $status)
            ->latest('requested_at')
            ->paginate(15)
            ->withQueryString();

        $counts = PartnerReactivationRequest::selectRaw('status, COUNT(*) total')
            ->groupBy('status')->pluck('total', 'status');

        return view('admin.partner-reactivations.index', compact('requests', 'counts', 'status'));
    }

    public function approve(Request $request, PartnerReactivationRequest $reactivation)
    {
        if ($reactivation->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        DB::transaction(function () use ($request, $reactivation) {
            $partner = User::role('partner')->lockForUpdate()->findOrFail($reactivation->user_id);
            User::where('id', $partner->id)
                ->orWhere('parent_partner_id', $partner->id)
                ->update(['status' => 'active', 'updated_at' => now()]);

            $reactivation->update([
                'status' => 'approved',
                'reviewed_at' => now(),
                'reviewed_by' => $request->user()->id,
                'admin_notes' => $request->input('admin_notes'),
            ]);
        });

        return back()->with('success', 'Partner account and its team have been reactivated.');
    }

    public function reject(Request $request, PartnerReactivationRequest $reactivation)
    {
        if ($reactivation->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $reactivation->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
            'admin_notes' => $request->validate(['admin_notes' => ['nullable', 'string', 'max:1000']])['admin_notes'] ?? null,
        ]);

        return back()->with('success', 'Reactivation request rejected; the account remains on hold.');
    }
}
