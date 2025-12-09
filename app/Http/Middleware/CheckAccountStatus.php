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

            // Check Status
            if ($user->status !== 'active') {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = match ($user->status) {
                    'pending' => 'Your account is pending Admin approval.',
                    'on_hold' => 'Your account has been put on hold. Please contact support.',
                    'restricted' => 'Your access has been restricted by the Administrator.',
                    default => 'Access denied.',
                };

                return redirect()->route('login')->withErrors(['email' => $message]);
            }
        }

        return $next($request);
    }
}