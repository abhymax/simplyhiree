<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use App\Services\SuperadminActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string'],
            'identifier' => ['nullable', 'string'],
        ]);

        $identifier = trim((string) ($validated['phone_number'] ?? $validated['identifier'] ?? $validated['email'] ?? ''));

        if ($identifier === '') {
            return back()->withErrors(['identifier' => 'Please provide email or mobile number.']);
        }

        if (!str_contains($identifier, '@')) {
            $digits = preg_replace('/\D+/', '', $identifier) ?: '';
            if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
                $digits = substr($digits, 1);
            }
            if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
                $digits = substr($digits, 2);
            }

            if (!preg_match('/^[6-9][0-9]{9}$/', $digits)) {
                return back()->withErrors(['phone_number' => 'Invalid Indian mobile number.']);
            }

            $profile = UserProfile::query()->with('user')->where('phone_number', $digits)->first();
            if (!$profile || !$profile->user) {
                return back()->withErrors(['phone_number' => 'No account found for this mobile number.']);
            }

            $temporaryPassword = strtoupper(substr(str_replace(['/', '+', '='], '', base64_encode(random_bytes(9))), 0, 10));

            $profile->user->forceFill([
                'password' => Hash::make($temporaryPassword),
                'remember_token' => Str::random(60),
            ])->save();

            $waResult = app(SuperadminActivityService::class)->sendForgotPasswordTemporaryPassword(
                user: $profile->user,
                phoneNumber: $digits,
                temporaryPassword: $temporaryPassword
            );

            if (!($waResult['ok'] ?? false)) {
                return back()->withErrors([
                    'identifier' => 'Could not send temporary password on WhatsApp right now. Please try again.',
                ]);
            }

            return back()->with('status', 'Temporary password sent on your WhatsApp number.');
        }

        $status = Password::sendResetLink(['email' => $identifier]);

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }
}
