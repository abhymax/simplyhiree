<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\PartnerProfile;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\SuperadminActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    public function redirectToGoogle(Request $request)
    {
        $role = $request->query('role');

        if ($role && !in_array($role, array('candidate', 'partner', 'client'), true)) {
            return redirect()->route('login')->with('error', 'Invalid role selected for Google sign-in.');
        }

        session(array('social_role' => $role));

        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $requestedRole = session('social_role');

            $user = User::where('google_id', $googleUser->id)
                ->orWhere('email', $googleUser->email)
                ->first();

            if ($user) {
                if (!$user->google_id) {
                    $user->update(array('google_id' => $googleUser->id));
                }

                $existingRole = $this->getPrimaryRole($user);

                if ($requestedRole && $existingRole && $requestedRole !== $existingRole) {
                    return redirect()->route('login')->with(
                        'error',
                        'This email is already registered as '.$existingRole.'. Please sign in with that same role.'
                    );
                }

                $this->ensureProfileForRole($user, $existingRole);

                Auth::login($user);

                return redirect()->route($this->dashboardRouteForRole($existingRole));
            }

            $newRole = $requestedRole ? $requestedRole : 'candidate';

            $newUser = User::create(array(
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'password' => null,
                'status' => in_array($newRole, array('partner', 'client'), true) ? 'pending' : 'active',
                'billable_period_days' => 30,
            ));

            $newUser->assignRole($newRole);
            $this->ensureProfileForRole($newUser, $newRole);
            app(SuperadminActivityService::class)->logUserSignup($newUser, $newRole, 'google_web');

            Auth::login($newUser);

            if (in_array($newRole, array('partner', 'client'), true) && $newUser->status === 'pending') {
                Auth::logout();
                return redirect()->route('login')->with('status', 'Registration successful! Your account is pending Admin approval.');
            }

            return redirect()->route($this->dashboardRouteForRole($newRole));
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Google Login failed. Please try again.');
        }
    }

    private function getPrimaryRole(User $user)
    {
        if ($user->hasRole('Superadmin') || $user->hasRole('Manager')) {
            return 'admin';
        }

        if ($user->hasRole('client')) {
            return 'client';
        }

        if ($user->hasRole('partner')) {
            return 'partner';
        }

        if ($user->hasRole('candidate')) {
            return 'candidate';
        }

        return 'candidate';
    }

    private function dashboardRouteForRole($role)
    {
        if ($role === 'client') {
            return 'client.dashboard';
        }

        if ($role === 'partner') {
            return 'partner.dashboard';
        }

        if ($role === 'admin') {
            return 'admin.dashboard';
        }

        return 'candidate.dashboard';
    }

    private function ensureProfileForRole(User $user, $role)
    {
        if ($role === 'partner' && !$user->partnerProfile) {
            PartnerProfile::create(array(
                'user_id' => $user->id,
                'company_type' => 'Freelancer',
            ));
        } elseif ($role === 'client' && !$user->clientProfile) {
            ClientProfile::create(array('user_id' => $user->id));
        } elseif ($role === 'candidate' && !$user->profile) {
            UserProfile::create(array('user_id' => $user->id));
        }
    }
}
