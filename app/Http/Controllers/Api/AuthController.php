<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\PartnerProfile;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'regex:/^[6-9][0-9]{9}$/', 'unique:user_profiles,phone_number'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => 'active',
        ]);

        // Mobile self-registration is candidate-only.
        $user->assignRole('candidate');

        UserProfile::create([
            'user_id' => $user->id,
            'phone_number' => $validated['phone_number'],
        ]);

        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser && !$existingUser->password && $existingUser->google_id) {
            return response()->json([
                'message' => 'This account uses Google login. Please continue with Google.',
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $user = $request->user();

        // Only admin/partner/client/candidate can use mobile app
        if (!($user->hasRole('Superadmin') || $user->hasRole('Manager') || $user->hasRole('partner') || $user->hasRole('client') || $user->hasRole('candidate'))) {
            Auth::logout();
            return response()->json(['message' => 'Role not allowed for mobile app.'], 403);
        }

        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $this->getPrimaryRole($user),
            ],
        ]);
    }

    public function googleLogin(Request $request)
    {
        $validated = $request->validate([
            'access_token' => ['required', 'string'],
            'role' => ['nullable', 'in:candidate,partner,client,admin'],
        ]);

        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->userFromToken($validated['access_token']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Google authentication failed. Please try again.',
            ], 422);
        }

        $requestedRole = $validated['role'] ?? null;

        $user = User::where('google_id', $googleUser->id)
            ->orWhere('email', $googleUser->email)
            ->first();

        if ($user) {
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->id]);
            }

            $existingRole = $this->getPrimaryRole($user);

            if (!in_array($existingRole, ['admin', 'partner', 'client', 'candidate'], true)) {
                return response()->json(['message' => 'Role not allowed for mobile app.'], 403);
            }

            if ($requestedRole && $existingRole && $requestedRole !== $existingRole) {
                return response()->json([
                    'message' => "This email is already registered as {$existingRole}. Please sign in with that role.",
                ], 422);
            }

            if (in_array($user->status, ['pending', 'restricted', 'on_hold'], true)) {
                return response()->json([
                    'message' => "Your account status is '{$user->status}'. Please contact support.",
                ], 403);
            }

            $this->ensureProfileForRole($user, $existingRole);
            $token = $user->createToken('mobile-token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $existingRole,
                ],
            ]);
        }

        if ($requestedRole === 'admin') {
            return response()->json(['message' => 'Admin account cannot be self-registered from Google login.'], 403);
        }

        $newRole = $requestedRole ?: 'candidate';
        $newUser = User::create([
            'name' => $googleUser->name ?? 'Google User',
            'email' => $googleUser->email,
            'google_id' => $googleUser->id,
            'password' => null,
            'status' => in_array($newRole, ['partner', 'client'], true) ? 'pending' : 'active',
            'billable_period_days' => 30,
        ]);

        $newUser->assignRole($newRole);
        $this->ensureProfileForRole($newUser, $newRole);

        if (in_array($newRole, ['partner', 'client'], true) && $newUser->status === 'pending') {
            return response()->json([
                'message' => 'Registration successful! Your account is pending Admin approval.',
            ], 403);
        }

        $token = $newUser->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $newUser->id,
                'name' => $newUser->name,
                'email' => $newUser->email,
                'role' => $newRole,
            ],
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $this->getPrimaryRole($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => __($status),
            ], 422);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status),
            ], 422);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }

    private function getPrimaryRole(User $user): string
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

        return 'candidate';
    }

    private function ensureProfileForRole(User $user, string $role): void
    {
        if ($role === 'partner' && !$user->partnerProfile) {
            PartnerProfile::create([
                'user_id' => $user->id,
                'company_type' => 'Freelancer',
            ]);
        } elseif ($role === 'client' && !$user->clientProfile) {
            ClientProfile::create(['user_id' => $user->id]);
        } elseif ($role === 'candidate' && !$user->profile) {
            UserProfile::create(['user_id' => $user->id]);
        }
    }
}
