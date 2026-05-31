<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Superadmins are always allowed
            if ($user->hasRole('Superadmin')) {
                return $next($request);
            }

            // Auto-hold check and routing restrictions for partner role
            if ($user->hasRole('partner')) {
                $owner = $user->parent_partner_id ? \App\Models\User::find($user->parent_partner_id) : $user;

                if ($owner && $owner->status === 'active') {
                    if ($owner->created_at && $owner->created_at <= now()->subDays(15)) {
                        $teamIds = \App\Models\User::where('parent_partner_id', $owner->id)->pluck('id')->push($owner->id)->all();

                        $hasActivity = \App\Models\JobApplication::whereIn('submitted_by_user_id', $teamIds)
                            ->where('created_at', '>=', now()->subDays(15))
                            ->exists() || \App\Models\Candidate::where('partner_id', $owner->id)
                            ->where('created_at', '>=', now()->subDays(15))
                            ->exists();

                        if (!$hasActivity) {
                            $owner->update(['status' => 'on_hold']);
                            if ($user->id !== $owner->id) {
                                $user->update(['status' => 'on_hold']);
                            }
                        }
                    }
                }

                if ($owner && $owner->status === 'on_hold') {
                    $routeName = optional($request->route())->getName();
                    if (in_array($routeName, ['partner.dashboard', 'dashboard'], true)) {
                        return $next($request);
                    }
                    return redirect()->route('partner.dashboard');
                }
            }

            // Check Status
            if ($user->status !== 'active') {
                $message = match ($user->status) {
                    'pending'    => 'Your account is pending Admin approval.',
                    'on_hold'    => 'Your account has been put on hold. Please contact support.',
                    'restricted' => 'Your access has been restricted by the Administrator.',
                    default      => 'Access denied.',
                };

                if ($request->expectsJson()) {
                    return response()->json(['message' => $message], 403);
                }

                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors(['email' => $message]);
            }
        }

        return $next($request);
    }
}