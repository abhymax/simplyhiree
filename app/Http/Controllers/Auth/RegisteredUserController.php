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
     * Handle partner registration using the Spatie role system.
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
        ]);
        
        $user->assignRole('partner');

        event(new Registered($user));
        Auth::login($user);
        return redirect()->route('partner.dashboard');
    }

    /**
     * Show the candidate registration form.
     */
    public function showCandidateRegistrationForm(): View
    {
        return view('auth.register_candidate');
    }

    /**
     * Handle candidate registration using the Spatie role system.
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
        ]);

        $user->assignRole('candidate');

        event(new Registered($user));
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
     * Handle client registration using the Spatie role system.
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
        ]);

        $user->assignRole('client');

        event(new Registered($user));
        Auth::login($user);
        return redirect()->route('client.dashboard');
    }
}

