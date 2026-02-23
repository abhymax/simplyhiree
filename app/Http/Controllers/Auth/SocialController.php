<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\PartnerProfile;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\PhoneOtpService;
use App\Services\SuperadminActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    public function redirectToGoogle(Request $request)
    {
        $role = $request->query('role');

        if ($role && !in_array($role, ['candidate', 'partner', 'client', 'admin'], true)) {
            return redirect()->route('login')->with('error', 'Invalid role selected for Google sign-in.');
        }

        // Role is optional from login page; if missing we infer from existing account.
        session(['social_role' => $role]);

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
                        'This email is already registered as ' . $existingRole . '. Please sign in with that same role.'
                    );
                }

                if (!in_array($existingRole, ['admin', 'candidate', 'partner', 'client'], true)) {
                    return redirect()->route('login')->with(
                        'error',
                        'Role not allowed for Google login.'
                    );
                }

                if (in_array($user->status, ['pending', 'restricted', 'on_hold', 'inactive'], true)) {
                    return redirect()->route('login')->with(
                        'error',
                        "Your account status is '{$user->status}'. Please contact support."
                    );
                }

                $this->ensureProfileForRole($user, $existingRole);
                session([
                    'google_verify_user_id' => $user->id,
                    'google_verify_role' => $existingRole,
                ]);

                return redirect()->route('google.phone.verify');
            }

            $newRole = $requestedRole ?: 'candidate';

            if ($newRole === 'admin') {
                return redirect()->route('login')->with(
                    'error',
                    'Admin account cannot be self-registered from Google signup.'
                );
            }

            $newUser = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'password' => null,
                'status' => in_array($newRole, ['partner', 'client'], true) ? 'pending' : 'active',
                'billable_period_days' => 30,
            ]);

            $newUser->assignRole($newRole);
            $this->ensureProfileForRole($newUser, $newRole);
            app(SuperadminActivityService::class)->logUserSignup($newUser, $newRole, 'google_web');
            session([
                'google_verify_user_id' => $newUser->id,
                'google_verify_role' => $newRole,
            ]);

            return redirect()->route('google.phone.verify');
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Google Login failed. Please try again.');
        }
    }

    public function showGooglePhoneVerificationForm()
    {
        $user = $this->pendingGoogleVerificationUser();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Google verification session expired. Please login again.');
        }
        $role = (string) session('google_verify_role', $this->getPrimaryRole($user));

        return view('auth.google_candidate_otp', [
            'userName' => $user->name,
            'phoneNumber' => $user->profile?->phone_number ?? '',
            'otpPurpose' => 'google_login',
            'otpRole' => $role,
        ]);
    }

    public function completeGooglePhoneVerification(Request $request, PhoneOtpService $otpService)
    {
        $request->validate([
            'phone_number' => ['required', 'regex:/^[6-9][0-9]{9}$/'],
            'otp_verification_token' => ['required', 'string'],
        ]);

        $user = $this->pendingGoogleVerificationUser();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Google verification session expired. Please login again.');
        }
        $role = (string) session('google_verify_role', $this->getPrimaryRole($user));

        $phone = $otpService->normalizePhone($request->phone_number);
        if (!$phone) {
            return back()->withInput()->withErrors([
                'phone_number' => 'Enter a valid 10-digit Indian mobile number.',
            ]);
        }

        $token = trim((string) $request->input('otp_verification_token'));
        $verified = $otpService->consumeVerificationToken(
            phoneNumber: $phone,
            verificationToken: $token,
            purpose: 'google_login',
            role: $role
        );

        if (!$verified && $role === 'candidate') {
            // Backward compatibility with old candidate-only OTP purpose.
            $verified = $otpService->consumeVerificationToken(
                phoneNumber: $phone,
                verificationToken: $token,
                purpose: 'google_candidate_login',
                role: 'candidate'
            );
        }

        if (!$verified) {
            return back()->withInput()->withErrors([
                'phone_number' => 'OTP verification expired or invalid. Please verify again.',
            ]);
        }

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            ['phone_number' => $phone]
        );

        if (in_array($role, ['partner', 'client'], true) && $user->status === 'pending') {
            $this->clearGoogleVerificationSession();
            return redirect()->route('login')->with(
                'status',
                'Registration successful. Your account is pending admin approval.'
            );
        }

        Auth::login($user);
        $this->clearGoogleVerificationSession();

        return redirect()->route($this->dashboardRouteForRole($role))->with('status', 'Phone verified successfully.');
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
        if (!$user->profile) {
            UserProfile::create(['user_id' => $user->id]);
        }

        if ($role === 'partner' && !$user->partnerProfile) {
            PartnerProfile::create([
                'user_id' => $user->id,
                'company_type' => 'Freelancer',
            ]);
        } elseif ($role === 'client' && !$user->clientProfile) {
            ClientProfile::create(['user_id' => $user->id]);
        }
    }

    private function pendingGoogleVerificationUser(): ?User
    {
        $userId = (int) session('google_verify_user_id', 0);
        if ($userId <= 0) {
            return null;
        }

        $user = User::with('profile')->find($userId);
        if (!$user) {
            $this->clearGoogleVerificationSession();
            return null;
        }

        $role = $this->getPrimaryRole($user);
        if (!in_array($role, ['admin', 'candidate', 'partner', 'client'], true)) {
            $this->clearGoogleVerificationSession();
            return null;
        }

        return $user;
    }

    private function clearGoogleVerificationSession(): void
    {
        session()->forget(['social_role', 'google_verify_user_id', 'google_verify_role']);
    }
}
