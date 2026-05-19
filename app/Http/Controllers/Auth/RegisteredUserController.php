<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\ClientProfile;
use App\Models\PartnerProfile;
use App\Services\PhoneOtpService;
use App\Services\SuperadminActivityService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Show the partner registration form.
     */
    public function showPartnerRegistrationForm(Request $request): View
    {
        $inviteToken  = $request->query('invite');
        $invitedBy    = null;
        $prefillName  = null;
        $prefillEmail = null;
        $prefillPhone = null;

        if ($inviteToken) {
            $invite = \App\Models\ClientVendorInvitation::where('invite_token', $inviteToken)
                ->where('status', 'pending')
                ->first();
            if ($invite) {
                $invitedBy    = optional(\App\Models\User::find($invite->client_id))->name;
                $prefillName  = $invite->name;
                $prefillEmail = $invite->email;
                $prefillPhone = $invite->phone;
            } else {
                $inviteToken = null; // bad/used token; treat as plain signup
            }
        }

        return view('auth.register_partner', compact(
            'inviteToken', 'invitedBy', 'prefillName', 'prefillEmail', 'prefillPhone'
        ));
    }

    /**
     * Handle partner registration.
     * Partners are created as 'pending' and require approval.
     */
    public function registerPartner(
        Request $request,
        SuperadminActivityService $activityService,
        PhoneOtpService $otpService
    ): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'regex:/^[6-9][0-9]{9}$/', 'unique:user_profiles,phone_number'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'company_type' => ['required', 'string', 'in:Placement Agency,Freelancer,Recruiter'],
            'otp_verification_token' => ['nullable', 'string'],
            'invite_token' => ['nullable', 'string', 'size:40'],
        ]);

        // Resolve the invite (if any) BEFORE creating the user so we can hook
        // the new partner to the inviting client.
        $invite = null;
        if ($request->filled('invite_token')) {
            $invite = \App\Models\ClientVendorInvitation::where('invite_token', $request->invite_token)
                ->where('status', 'pending')
                ->first();
        }

        $phone = $otpService->normalizePhone($request->phone_number);
        $verificationToken = trim((string) $request->input('otp_verification_token', ''));
        $verified = $phone && $verificationToken !== ''
            ? $otpService->consumeVerificationToken($phone, $verificationToken, 'registration', 'partner')
            : false;

        if (!$verified) {
            return back()
                ->withInput()
                ->withErrors(['phone_number' => 'Please verify your mobile number with OTP before registering.']);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'pending', // Default to Pending
        ]);
        
        $user->assignRole('partner');

        UserProfile::create([
            'user_id' => $user->id,
            'phone_number' => $phone,
        ]);

        PartnerProfile::create([
            'user_id' => $user->id,
            'company_type' => $request->company_type,
        ]);

        event(new Registered($user));
        $activityService->logUserSignup($user, 'partner', 'web');

        // If this partner came in via a client's invite link, link them to
        // that client and mark the invitation as joined.
        if ($invite) {
            try {
                $invite->update([
                    'status'            => 'joined',
                    'joined_partner_id' => $user->id,
                    'joined_at'         => now(),
                ]);
                // Also auto-add to the inviting client's preferred vendors
                // so they show up immediately in the client's Vendors page.
                $client = \App\Models\User::find($invite->client_id);
                if ($client && method_exists($client, 'preferredVendors')) {
                    $client->preferredVendors()->syncWithoutDetaching([
                        $user->id => ['added_at' => now()],
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::warning('Failed to link partner ' . $user->id . ' to invite ' . $invite->id . ': ' . $e->getMessage());
            }
        }

        // Do NOT login automatically. Redirect to login with a message.
        return redirect()->route('login')->with('status', 'Registration successful! Your account is pending Admin approval.');
    }

    // ... [Remaining methods for Candidate and Client unchanged] ...
    
    public function showCandidateRegistrationForm(): View
    {
        return view('auth.register_candidate');
    }

    public function registerCandidate(
        Request $request,
        SuperadminActivityService $activityService,
        PhoneOtpService $otpService
    ): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'regex:/^[6-9][0-9]{9}$/', 'unique:user_profiles,phone_number'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'otp_verification_token' => ['nullable', 'string'],
        ]);

        $phone = $otpService->normalizePhone($request->phone_number);
        $verificationToken = trim((string) $request->input('otp_verification_token', ''));
        $verified = $phone && $verificationToken !== ''
            ? $otpService->consumeVerificationToken($phone, $verificationToken, 'registration', 'candidate')
            : false;

        if (!$verified) {
            return back()
                ->withInput()
                ->withErrors(['phone_number' => 'Please verify your mobile number with OTP before registering.']);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'active', 
        ]);

        $user->assignRole('candidate');

        UserProfile::create([
            'user_id' => $user->id,
            'phone_number' => $phone,
        ]);

        event(new Registered($user));
        $activityService->logUserSignup($user, 'candidate', 'web');

        Auth::login($user);

        return redirect()->route('candidate.dashboard');
    }

    public function showClientRegistrationForm(): View
    {
        return view('auth.register_client');
    }

    public function registerClient(
        Request $request,
        SuperadminActivityService $activityService,
        PhoneOtpService $otpService
    ): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'official_email' => ['required', 'string', 'email', 'max:255'],
            'service_required' => ['required', 'string', 'in:Profession Staffing,Contract Staffing,RPO,Others'],
            'phone_number' => ['required', 'regex:/^[6-9][0-9]{9}$/', 'unique:user_profiles,phone_number'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'otp_verification_token' => ['nullable', 'string'],
        ]);

        $phone = $otpService->normalizePhone($request->phone_number);
        $verificationToken = trim((string) $request->input('otp_verification_token', ''));
        $verified = $phone && $verificationToken !== ''
            ? $otpService->consumeVerificationToken($phone, $verificationToken, 'registration', 'client')
            : false;

        if (!$verified) {
            return back()
                ->withInput()
                ->withErrors(['phone_number' => 'Please verify your mobile number with OTP before registering.']);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'billable_period_days' => 30, 
            'status' => 'pending', 
        ]);

        $user->assignRole('client');
        UserProfile::create([
            'user_id' => $user->id,
            'phone_number' => $phone,
        ]);
        ClientProfile::create([
            'user_id' => $user->id,
            'company_name' => $request->company_name,
            'official_email' => $request->official_email,
            'service_required' => $request->service_required,
        ]);

        event(new Registered($user));
        $activityService->logUserSignup($user, 'client', 'web');

        return redirect()->route('login')->with('status', 'Registration successful! Your account is pending Admin approval.');
    }
}
