<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceClientModule
{
    /**
     * Owner-only route names — client team members can never access these,
     * whatever modules they hold. Financial/commercial, company profile,
     * and team management stay with the account owner.
     */
    private const OWNER_ONLY = [
        'client.team.index', 'client.team.store', 'client.team.update',
        'client.team.toggle', 'client.team.destroy',
    ];

    /**
     * Route name => module key. A member must hold the module to reach the
     * route. Unlisted routes (dashboard, notifications, read-only lists /
     * detail views) are baseline-allowed for every member.
     */
    private const MODULE_ROUTES = [
        // Job posting
        'client.jobs.create' => 'job_posting',
        'client.jobs.store' => 'job_posting',
        'client.jobs.edit' => 'job_posting',
        'client.jobs.update' => 'job_posting',
        'client.jobs.status.update' => 'job_posting',
        'client.jobs.destroy' => 'job_posting',
        'client.jobs.request-deactivation' => 'job_posting',
        'client.jobs.cancel-deactivation' => 'job_posting',
        'client.broadcasts.store' => 'job_posting',
        'client.broadcasts.retry' => 'job_posting',

        // Applicants & review
        'client.applications.reject' => 'applicants',
        'client.applications.shortlist' => 'applicants',
        'client.applications.maybe' => 'applicants',
        'client.applications.undo-review' => 'applicants',

        // Interviews
        'client.applications.interview.create' => 'interviews',
        'client.applications.interview.store' => 'interviews',
        'client.applications.interview.edit' => 'interviews',
        'client.applications.interview.update' => 'interviews',
        'client.applications.interview.appeared' => 'interviews',
        'client.applications.interview.noshow' => 'interviews',
        'client.applications.feedback.create' => 'interviews',
        'client.applications.feedback.store' => 'interviews',
        'client.applications.rounds.create' => 'interviews',
        'client.applications.rounds.store' => 'interviews',
        'client.rounds.edit' => 'interviews',
        'client.rounds.update' => 'interviews',
        'client.rounds.appeared' => 'interviews',
        'client.rounds.noshow' => 'interviews',
        'client.rounds.feedback.create' => 'interviews',
        'client.rounds.feedback' => 'interviews',

        // Selection & joining
        'client.applications.select.show' => 'selection',
        'client.applications.select.edit' => 'selection',
        'client.applications.select.store' => 'selection',
        'client.applications.select.update' => 'selection',
        'client.applications.markJoined' => 'selection',
        'client.applications.markNotJoined' => 'selection',
        'client.applications.rate' => 'selection',
        'client.applications.rate.store' => 'selection',
        'client.applications.showLeftForm' => 'selection',
        'client.applications.markLeft' => 'selection',
        'client.applications.request-replacement' => 'selection',
        'client.replacements.index' => 'selection',

        // Vendors
        'client.vendors.browse' => 'vendors',
        'client.vendors.toggle' => 'vendors',
        'client.vendors.invite' => 'vendors',
        'client.vendors.invite.store' => 'vendors',
        'client.vendors.invite.email' => 'vendors',
        'client.vendors.assign-request' => 'vendors',
        'client.vendors.assign-request.store' => 'vendors',
        'client.vendors.performance' => 'vendors',

        // Invoices & billing (grantable to members)
        'client.billing' => 'billing',
        'client.billing.demo-invoice' => 'billing',
        'client.billing.markPaid' => 'billing',
        'client.billing.unmarkPaid' => 'billing',

        // Company settings (grantable to members)
        'client.profile.company' => 'company',
        'client.profile.update' => 'company',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('client')) {
            return $next($request);
        }

        // Account owner has unrestricted access.
        if (empty($user->parent_partner_id)) {
            return $next($request);
        }

        $routeName = optional($request->route())->getName();
        if (!$routeName) {
            return $next($request);
        }

        if (in_array($routeName, self::OWNER_ONLY, true)) {
            abort(403, 'This section is restricted to the client account owner. Please ask your account owner.');
        }

        $module = self::MODULE_ROUTES[$routeName] ?? null;
        if ($module !== null && !$user->hasClientModule($module)) {
            abort(403, 'Your account does not have access to this module. Please ask your client account owner to enable it.');
        }

        return $next($request);
    }
}
