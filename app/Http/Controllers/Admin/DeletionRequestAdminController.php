<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountDeletionRequest;
use App\Models\User;
use App\Services\AccountEraser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Admin side of the account-deletion flow. Requests were being collected with
 * nothing to action them, so they sat unseen.
 */
class DeletionRequestAdminController extends Controller
{
    public function index(Request $request, AccountEraser $eraser)
    {
        $query = AccountDeletionRequest::query()->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(fn ($q) => $q->where('email', 'like', "%{$s}%")
                                       ->orWhere('name', 'like', "%{$s}%")
                                       ->orWhere('reference', 'like', "%{$s}%"));
        }

        $requests = $query->paginate(20)->withQueryString();

        // Attach the live account (if it still exists) and what erasing removes.
        $accounts = [];
        $previews = [];
        foreach ($requests as $r) {
            $user = $r->user_id
                ? User::find($r->user_id)
                : User::whereRaw('LOWER(email) = ?', [strtolower((string) $r->email)])->first();

            $accounts[$r->id] = $user;
            $previews[$r->id] = $user ? $eraser->preview($user) : null;
        }

        return view('admin.deletion_requests.index', [
            'requests' => $requests,
            'accounts' => $accounts,
            'previews' => $previews,
            'counts'   => [
                'all'     => AccountDeletionRequest::count(),
                'pending' => AccountDeletionRequest::whereIn('status', ['pending_verification', 'pending'])->count(),
                'done'    => AccountDeletionRequest::where('status', 'completed')->count(),
            ],
        ]);
    }

    /** Permanently erase the account behind a request. */
    public function erase(Request $request, AccountDeletionRequest $deletionRequest, AccountEraser $eraser)
    {
        abort_unless(Auth::user()->hasRole('Superadmin'), 403, 'Only a Superadmin can erase an account.');

        $request->validate(['confirm' => 'required|in:ERASE']);

        $user = $deletionRequest->user_id
            ? User::find($deletionRequest->user_id)
            : User::whereRaw('LOWER(email) = ?', [strtolower((string) $deletionRequest->email)])->first();

        if (!$user) {
            $deletionRequest->update(['status' => 'completed']);

            return back()->with('success', 'No matching account remained — request closed.');
        }

        $result = $eraser->erase($user);

        if (!$result['ok']) {
            return back()->with('error', $result['message']);
        }

        $deletionRequest->update(['status' => 'completed']);

        Log::info('Account erased via deletion request', [
            'request_id' => $deletionRequest->id,
            'user_id'    => $user->id,
            'by'         => Auth::id(),
            'backup'     => $result['backup'],
        ]);

        $removed = collect($result['counts'])->map(fn ($n, $k) => "{$n} " . str_replace('_', ' ', $k))->implode(', ');

        return back()->with('success', 'Account erased. Removed: ' . ($removed ?: 'account record') . '.');
    }

    public function reject(Request $request, AccountDeletionRequest $deletionRequest)
    {
        abort_unless(Auth::user()->hasRole('Superadmin'), 403);

        $deletionRequest->update(['status' => 'rejected']);

        return back()->with('success', 'Request marked rejected. The account was left untouched.');
    }
}
