<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforcePartnerAccess
{
    /**
     * Owner-only route names — team members can never access these,
     * regardless of access_level. Includes team management, plan
     * upgrades, wallet, billing-side commercials, profile/business edit.
     */
    private const OWNER_ONLY = [
        'partner.team.index',
        'partner.team.store',
        'partner.team.update',
        'partner.team.toggle',
        'partner.upgrade',
        'partner.upgrade.request',
        'partner.upgrade.cancel',
        'partner.profile.business',
        'partner.profile.update',
        'partner.wallet',
        'partner.earnings',
    ];

    /**
     * Submission routes — blocked for view_only members.
     * Anything that creates or mutates candidate / application data.
     */
    private const SUBMISSION_ROUTES = [
        'partner.jobs.showApplyForm',
        'partner.jobs.submit',
        'partner.candidates.check',
        'partner.candidates.verify',
        'partner.candidates.create',
        'partner.candidates.store',
        'partner.candidates.edit',
        'partner.candidates.update',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('partner')) {
            return $next($request);
        }

        // Owner has unrestricted access
        if (empty($user->parent_partner_id)) {
            return $next($request);
        }

        $routeName = optional($request->route())->getName();
        if (!$routeName) {
            return $next($request);
        }

        $access = $user->access_level ?: 'full';

        // 1. Owner-only routes — block every team member
        if (in_array($routeName, self::OWNER_ONLY, true)) {
            abort(403, 'This action is restricted to the partner account owner. Please ask your account owner.');
        }

        // 2. view_only: block any submission/creation routes
        if ($access === 'view_only' && in_array($routeName, self::SUBMISSION_ROUTES, true)) {
            abort(403, 'You have view-only access. Submitting or editing candidates is disabled for this account.');
        }

        // 3. view_only: additionally block any non-GET request
        if ($access === 'view_only' && !$request->isMethodSafe()) {
            abort(403, 'You have view-only access. Write actions are disabled for this account.');
        }

        return $next($request);
    }
}
