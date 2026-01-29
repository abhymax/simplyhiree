<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PartnerProfile; //
use App\Models\ClientProfile; //
use App\Models\UserProfile;   //
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    /**
     * Redirect to Google. 
     * Accepts a 'role' query param (candidate, client, partner).
     */
    public function redirectToGoogle(Request $request)
    {
        // 1. Store the intended role in the session (Default to candidate if missing)
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

            // 1. Check if user already exists
            $user = User::where('google_id', $googleUser->id)
                        ->orWhere('email', $googleUser->email)
                        ->first();

            if ($user) {
                // UPDATE EXISTING USER
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->id]);
                }
                Auth::login($user);
                
                // Redirect based on role
                if ($user->hasRole('client')) return redirect()->route('client.dashboard');
                if ($user->hasRole('partner')) return redirect()->route('partner.dashboard');
                return redirect()->route('candidate.dashboard');
            } 

            // 2. REGISTER NEW USER
            $role = session('social_role', 'candidate'); // Retrieve stored role

            $newUser = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'password' => null, // No password for Google users
                'status' => 'active',
                'billable_period_days' => 30, // Default for clients
            ]);

            $newUser->assignRole($role);

            // 3. Create Empty Profiles based on Role (To prevent errors later)
            if ($role === 'client') {
                ClientProfile::create(['user_id' => $newUser->id]);
            } elseif ($role === 'partner') {
                // Partner requires company_type usually, setting default 'Freelancer' to avoid crash
                PartnerProfile::create([
                    'user_id' => $newUser->id, 
                    'company_type' => 'Freelancer' 
                ]); 
            } else {
                UserProfile::create(['user_id' => $newUser->id]);
            }

            Auth::login($newUser);

            // 4. Redirect to Dashboard
            if ($role === 'client') return redirect()->route('client.dashboard');
            if ($role === 'partner') return redirect()->route('partner.dashboard');
            return redirect()->route('candidate.dashboard');

        } catch (\Exception $e) {
            dd($e->getMessage()); // <--- Run this FIRST to see the error on screen
            return redirect()->route('login')->with('error', 'Google Login failed. Please try again.');
        }
    }
}