<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\PartnerProfile;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\SuperadminActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request, SuperadminActivityService $activityService)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'regex:/^[6-9][0-9]{9}$/', 'unique:user_profiles,phone_number'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', 'in:candidate,partner,client'],
            'company_type' => ['nullable', 'in:Placement Agency,Freelancer,Recruiter'],
        ]);

        $role = $validated['role'] ?? 'candidate';
        $isPendingRole = in_array($role, ['partner', 'client'], true);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $isPendingRole ? 'pending' : 'active',
            'billable_period_days' => $role === 'client' ? 30 : null,
        ]);

        $user->assignRole($role);

        UserProfile::create([
            'user_id' => $user->id,
            'phone_number' => $validated['phone_number'],
        ]);

        if ($role === 'partner') {
            PartnerProfile::create([
                'user_id' => $user->id,
                'company_type' => $validated['company_type'] ?? 'Freelancer',
            ]);
        } elseif ($role === 'client') {
            ClientProfile::create([
                'user_id' => $user->id,
            ]);
        }

        $activityService->logUserSignup($user, $role, 'mobile');

        if ($isPendingRole) {
            return response()->json([
                'message' => 'Registration successful! Your account is pending Admin approval.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $role,
                    'status' => $user->status,
                ],
            ], 201);
        }

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

        if (in_array($user->status, ['pending', 'restricted', 'on_hold', 'inactive'], true)) {
            Auth::logout();
            return response()->json([
                'message' => "Your account status is '{$user->status}'. Please contact support.",
            ], 403);
        }

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

    public function googleLogin(Request $request, SuperadminActivityService $activityService)
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

            if (in_array($user->status, ['pending', 'restricted', 'on_hold', 'inactive'], true)) {
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
        $activityService->logUserSignup($newUser, $newRole, 'google_mobile');

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
        $validated = $request->validate([
            'email' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string'],
            'identifier' => ['nullable', 'string'],
        ]);

        $identifier = trim((string) ($validated['phone_number'] ?? $validated['identifier'] ?? $validated['email'] ?? ''));

        if ($identifier === '') {
            return response()->json(['message' => 'Please provide email or phone number.'], 422);
        }

        // Mobile-number based recovery via WhatsApp temporary password.
        if (!str_contains($identifier, '@')) {
            $digits = preg_replace('/\D+/', '', $identifier) ?: '';
            if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
                $digits = substr($digits, 1);
            }
            if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
                $digits = substr($digits, 2);
            }

            if (!preg_match('/^[6-9][0-9]{9}$/', $digits)) {
                return response()->json(['message' => 'Invalid Indian mobile number.'], 422);
            }

            $profile = UserProfile::query()->with('user')->where('phone_number', $digits)->first();
            if (!$profile || !$profile->user) {
                return response()->json(['message' => 'No account found for this mobile number.'], 404);
            }

            $temporaryPassword = strtoupper(substr(str_replace(['/', '+', '='], '', base64_encode(random_bytes(9))), 0, 10));

            $profile->user->forceFill([
                'password' => Hash::make($temporaryPassword),
                'remember_token' => Str::random(60),
            ])->save();

            app(SuperadminActivityService::class)->sendForgotPasswordTemporaryPassword(
                user: $profile->user,
                phoneNumber: $digits,
                temporaryPassword: $temporaryPassword
            );

            return response()->json([
                'message' => 'A temporary password has been sent to your WhatsApp number.',
            ]);
        }

        // Email fallback (existing flow).
        $status = Password::sendResetLink(['email' => $identifier]);

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
