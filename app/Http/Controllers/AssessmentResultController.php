<?php

namespace App\Http\Controllers;

use App\Models\AssessmentSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessmentResultController extends Controller
{
    private function isAdmin(): bool
    {
        return request()->routeIs('admin.*');
    }

    private function views(): string
    {
        return $this->isAdmin() ? 'admin.assessment_results' : 'client.assessment_results';
    }

    private function routeNames(): array
    {
        $p = $this->isAdmin() ? 'admin.assessment-results' : 'client.assessment-results';
        return ['index' => "$p.index", 'show' => "$p.show"];
    }

    /** Base query scoped by access: admin sees all; a client sees only their own APPROVED jobs. */
    private function scopedQuery()
    {
        $query = AssessmentSession::query()->with(['job', 'candidate', 'partner']);

        if (!$this->isAdmin()) {
            $ownerId = (int) Auth::user()->clientOwnerId();
            // Clients only see results for jobs that are live (approved by admin) —
            // never for jobs still pending approval / on hold / rejected.
            $query->whereHas('job', fn ($q) => $q->where('user_id', $ownerId)->where('status', 'approved'));
        }

        return $query;
    }

    public function index(Request $request)
    {
        $base = $this->scopedQuery();

        $counts = [
            'all'         => (clone $base)->count(),
            'in_progress' => (clone $base)->whereIn('status', ['pending', 'verified', 'in_progress'])->count(),
            'passed'      => (clone $base)->where('status', 'passed')->count(),
            'failed'      => (clone $base)->where('status', 'failed')->count(),
        ];

        $query = (clone $base);
        $filter = $request->input('status', 'all');
        if ($filter === 'in_progress') {
            $query->whereIn('status', ['pending', 'verified', 'in_progress']);
        } elseif (in_array($filter, ['passed', 'failed'], true)) {
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

        if ($request->filled('job_id')) {
            $query->where('job_id', (int) $request->input('job_id'));
        }

        // Company filter (admin only): show assessments for one client's jobs.
        $clientId = $this->isAdmin() && $request->filled('client_id') ? (int) $request->input('client_id') : null;
        if ($clientId) {
            $query->whereHas('job', fn ($jq) => $jq->where('user_id', $clientId));
        }

        $sessions = $query->latest()->paginate(25)->withQueryString();

        // Client dropdown for the admin company filter — only clients that
        // actually have assessment sessions, so the list stays short.
        $clientOptions = collect();
        if ($this->isAdmin()) {
            $clientOptions = \App\Models\User::whereIn('id', function ($q) {
                $q->select('jobs.user_id')
                  ->from('assessment_sessions')
                  ->join('jobs', 'jobs.id', '=', 'assessment_sessions.job_id')
                  ->whereNotNull('jobs.user_id');
            })->orderBy('name')->get(['id', 'name']);
        }

        return view($this->views() . '.index', [
            'sessions'      => $sessions,
            'counts'        => $counts,
            'filter'        => $filter,
            'routes'        => $this->routeNames(),
            'isAdmin'       => $this->isAdmin(),
            'clientOptions' => $clientOptions,
            'clientId'      => $clientId,
        ]);
    }

    public function show(AssessmentSession $session)
    {
        // Access control: a client may only open sessions on their own APPROVED jobs.
        if (!$this->isAdmin()) {
            $ownerId = (int) Auth::user()->clientOwnerId();
            $job = $session->job;
            if (!$job || (int) $job->user_id !== $ownerId || $job->status !== 'approved') {
                abort(403);
            }
        }

        $session->load([
            'job.assessmentStages.assessment',
            'candidate',
            'partner',
            'attempts' => fn ($q) => $q->orderBy('stage_order')->orderBy('attempt_number'),
            'attempts.assessment',
            'attempts.answers.question.options',
            'attempts.answers.option',
        ]);

        return view($this->views() . '.show', [
            'session' => $session,
            'routes'  => $this->routeNames(),
            'isAdmin' => $this->isAdmin(),
        ]);
    }
}
