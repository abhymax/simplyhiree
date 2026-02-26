<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();
        if ($user) {
            $this->repairLegacyAccess($user);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function repairLegacyAccess(User $user): void
    {
        // Backward compatibility for legacy users created before role/status normalization.
        if (empty($user->status)) {
            $user->status = 'active';
        }

        if (!$user->roles()->exists()) {
            $legacyRole = strtolower((string) ($user->getAttribute('role') ?? ''));
            $roleMap = [
                'superadmin' => 'Superadmin',
                'manager' => 'Manager',
                'partner' => 'partner',
                'client' => 'client',
                'candidate' => 'candidate',
            ];

            if (isset($roleMap[$legacyRole])) {
                try {
                    $user->assignRole($roleMap[$legacyRole]);
                } catch (\Throwable $e) {
                    Log::warning('Legacy role assignment failed during login.', [
                        'user_id' => $user->id,
                        'legacy_role' => $legacyRole,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        if ($user->isDirty('status')) {
            $user->save();
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
