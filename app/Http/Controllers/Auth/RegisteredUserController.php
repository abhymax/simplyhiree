<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'pending', // Default to Pending
        ]);
        
        $user->assignRole('partner');

        event(new Registered($user));

        // Do NOT login automatically. Redirect to login with a message.
        return redirect()->route('login')->with('status', 'Registration successful! Your account is pending Admin approval.');
    }

    /**
     * Show the candidate registration form.
     */
    public function showCandidateRegistrationForm(): View
    {
        return view('auth.register_candidate');
    }

    /**
     * Handle candidate registration.
     * Candidates are 'active' immediately.
     */
    public function registerCandidate(Request $request): RedirectResponse
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
            'status' => 'active', // Candidates are Active by default
        ]);

        $user->assignRole('candidate');

        event(new Registered($user));

        // Login immediately
        Auth::login($user);

        return redirect()->route('candidate.dashboard');
    }

    /**
     * Show the client registration form.
     */
    public function showClientRegistrationForm(): View
    {
        return view('auth.register_client');
    }

    /**
     * Handle client registration.
     * Clients are created as 'pending' and require approval.
     */
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
            'billable_period_days' => 30, // Default setting
            'status' => 'pending', // Default to Pending
        ]);

        $user->assignRole('client');

        event(new Registered($user));

        // Do NOT login automatically. Redirect to login with a message.
        return redirect()->route('login')->with('status', 'Registration successful! Your account is pending Admin approval.');
    }
}