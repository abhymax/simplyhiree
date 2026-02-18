<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\PartnerProfile; // <--- ADDED IMPORT
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
    public function showPartnerRegistrationForm(): View
    {
        return view('auth.register_partner');
    }

    /**
     * Handle partner registration.
     * Partners are created as 'pending' and require approval.
     */
    public function registerPartner(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // UPDATE: Validation for mandatory type
            'company_type' => ['required', 'string', 'in:Placement Agency,Freelancer,Recruiter'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'pending', // Default to Pending
        ]);
        
        $user->assignRole('partner');

        // UPDATE: Create the profile with the selected type immediately
        PartnerProfile::create([
            'user_id' => $user->id,
            'company_type' => $request->company_type,
        ]);

        event(new Registered($user));

        // Do NOT login automatically. Redirect to login with a message.
        return redirect()->route('login')->with('status', 'Registration successful! Your account is pending Admin approval.');
    }

    // ... [Remaining methods for Candidate and Client unchanged] ...
    
    public function showCandidateRegistrationForm(): View
    {
        return view('auth.register_candidate');
    }

    public function registerCandidate(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'regex:/^[6-9][0-9]{9}$/', 'unique:user_profiles,phone_number'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'active', 
        ]);

        $user->assignRole('candidate');

        UserProfile::create([
            'user_id' => $user->id,
            'phone_number' => $request->phone_number,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('candidate.dashboard');
    }

    public function showClientRegistrationForm(): View
    {
        return view('auth.register_client');
    }

    public function registerClient(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'billable_period_days' => 30, 
            'status' => 'pending', 
        ]);

        $user->assignRole('client');

        event(new Registered($user));

        return redirect()->route('login')->with('status', 'Registration successful! Your account is pending Admin approval.');
    }
}
