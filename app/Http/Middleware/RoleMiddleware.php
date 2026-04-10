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
            $roles = explode('|', $role);
            if (!auth()->check() || !auth()->user()->hasAnyRole($roles)) {
                abort(403, 'UNAUTHORIZED ACTION.');
            }
        }

        return $next($request);
    }
}

