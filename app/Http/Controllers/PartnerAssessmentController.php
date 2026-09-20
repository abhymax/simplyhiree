<?php

namespace App\Http\Controllers;

use App\Models\AssessmentSession;
use App\Services\AssessmentSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartnerAssessmentController extends Controller
{
    public function __construct(private AssessmentSessionService $sessions)
    {
    }

    private function ownerId(): int
    {
        return (int) Auth::user()->partnerOwnerId();
    }

    /** Tracking dashboard: every candidate this partner lined up for an assessment. */
    public function index(Request $request)
    {
        $ownerId = $this->ownerId();

        $base = AssessmentSession::where('partner_id', $ownerId);

        $counts = [
            'all'         => (clone $base)->count(),
            'pending'     => (clone $base)->whereIn('status', ['pending', 'verified'])->count(),
            'in_progress' => (clone $base)->where('status', 'in_progress')->count(),
            'passed'      => (clone $base)->where('status', 'passed')->count(),
            'failed'      => (clone $base)->where('status', 'failed')->count(),
        ];

        $query = (clone $base)->with([
            'job.assessmentStages.assessment',
            'candidate',
            'attempts',
        ]);

        $filter = $request->input('status', 'all');
        if ($filter === 'pending') {
            $query->whereIn('status', ['pending', 'verified']);
        } elseif (in_array($filter, ['in_progress', 'passed', 'failed'], true)) {
            $query->where('status', $filter);
        } else {
            $filter = 'all';
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('candidate_name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhereHas('job', fn ($jq) => $jq->where('title', 'like', "%{$s}%"));
            });
        }

        $sessions = $query->latest()->paginate(20)->withQueryString();

        return view('partner.assessments.index', [
            'sessions' => $sessions,
            'counts'   => $counts,
            'filter'   => $filter,
        ]);
    }

    /** Resend the candidate's magic-link invitation email. */
    public function resend(AssessmentSession $session)
    {
        if ((int) $session->partner_id !== $this->ownerId()) {
            abort(403);
        }

        if ($session->status === 'passed' || $session->status === 'failed') {
            return back()->with('error', 'This candidate has already completed the assessment.');
        }

        // Refresh the link expiry so a resend is always usable.
        if ($session->isLinkExpired()) {
            $session->update(['expires_at' => now()->addDays(AssessmentSession::LINK_TTL_DAYS)]);
        }

        $this->sessions->sendInvite($session);

        return back()->with('success', 'Assessment link re-sent to ' . $session->maskedEmail() . '.');
    }
}
