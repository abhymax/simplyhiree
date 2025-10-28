<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role = null): Response
    {
        if ($role) {
            // --- CORRECTED LOGIC ---
            // We now use the hasRole() method from the Spatie package,
            // which is the correct way to check for roles.
            if (!auth()->check() || !auth()->user()->hasRole($role)) {
                abort(403, 'UNAUTHORIZED ACTION.');
            }
        }

        return $next($request);
    }
}

