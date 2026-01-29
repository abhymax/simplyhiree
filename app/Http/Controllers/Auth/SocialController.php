<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    // Redirect to Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Handle Callback
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Find user by Google ID or Email
            $user = User::where('google_id', $googleUser->id)
                        ->orWhere('email', $googleUser->email)
                        ->first();

            if ($user) {
                // If user exists, update Google ID if missing
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->id]);
                }
                
                Auth::login($user);
                return redirect()->intended(route('dashboard'));
                
            } else {
                // Create new user (Default to Candidate role?)
                // NOTE: You need to decide which role a Google user gets by default.
                // Assuming 'candidate' for now.
                
                $newUser = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => null, // No password
                    'status' => 'active',
                ]);
                
                // Assign Default Role
                $newUser->assignRole('candidate'); 

                Auth::login($newUser);
                return redirect()->route('dashboard');
            }

        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Something went wrong with Google Login.');
        }
    }
}