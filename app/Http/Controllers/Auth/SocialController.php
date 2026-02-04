<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PartnerProfile;
use App\Models\ClientProfile;
use App\Models\UserProfile;
// use App\Models\Candidate; // Uncomment if you use a separate Candidate model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    /**
     * Redirect to Google. 
     */
    public function redirectToGoogle(Request $request)
    {
        // 1. Store the intended role in the session (Default to candidate)
        $role = $request->query('role', 'candidate'); 
        session(['social_role' => $role]);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google Callback.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            // Retrieve intended role from session (Default to candidate)
            $intendedRole = session('social_role', 'candidate');

            // 1. Check if user already exists
            $user = User::where('google_id', $googleUser->id)
                        ->orWhere('email', $googleUser->email)
                        ->first();

            if ($user) {
                // --- EXISTING USER LOGIC ---
                
                // Link Google ID if missing
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->id]);
                }

                // *** CRITICAL FIX: ASSIGN NEW ROLE IF MISSING ***
                if (!$user->hasRole($intendedRole)) {
                    $user->assignRole($intendedRole);
                    
                    // Create Empty Profile for the NEW role to prevent 500 Errors
                    if ($intendedRole === 'partner' && !$user->partnerProfile) {
                        PartnerProfile::create([
                            'user_id' => $user->id, 
                            'company_type' => 'Freelancer' // Default value
                        ]);
                    }
                    elseif ($intendedRole === 'client' && !$user->clientProfile) {
                        ClientProfile::create(['user_id' => $user->id]);
                    }
                    elseif ($intendedRole === 'candidate' && !$user->profile) {
                         UserProfile::create(['user_id' => $user->id]);
                         // If you have a 'candidates' table, you might need to create that record here too:
                         // Candidate::create(['user_id' => $user->id]); 
                    }
                }

                Auth::login($user);
                
                // *** FIX: Redirect based on INTENDED role, not just the first one found ***
                if ($intendedRole === 'client') return redirect()->route('client.dashboard');
                if ($intendedRole === 'partner') return redirect()->route('partner.dashboard');
                
                return redirect()->route('candidate.dashboard');
            } 

            // 2. REGISTER NEW USER
            $newUser = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'password' => null, 
                'status' => 'active',
                'billable_period_days' => 30, 
            ]);

            $newUser->assignRole($intendedRole);

            // 3. Create Profiles based on Role
            if ($intendedRole === 'client') {
                ClientProfile::create(['user_id' => $newUser->id]);
            } elseif ($intendedRole === 'partner') {
                PartnerProfile::create([
                    'user_id' => $newUser->id, 
                    'company_type' => 'Freelancer' 
                ]); 
            } else {
                UserProfile::create(['user_id' => $newUser->id]);
                // If you use a separate Candidate model, add it here:
                // Candidate::create(['user_id' => $newUser->id]);
            }

            Auth::login($newUser);

            // 4. Redirect
            if ($intendedRole === 'client') return redirect()->route('client.dashboard');
            if ($intendedRole === 'partner') return redirect()->route('partner.dashboard');
            return redirect()->route('candidate.dashboard');

        } catch (\Exception $e) {
            // Uncomment the line below to debug specific errors on screen
            // dd($e->getMessage()); 
            return redirect()->route('login')->with('error', 'Google Login failed. Please try again.');
        }
    }
}