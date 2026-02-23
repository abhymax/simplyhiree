<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PhoneOtpService
{
    private const OTP_TTL_MINUTES = 10;
    private const VERIFY_TTL_MINUTES = 20;
    private const RESEND_COOLDOWN_SECONDS = 30;

    public function __construct(
        private readonly AiSensyWhatsAppService $whatsApp
    ) {
    }

    public function sendOtp(string $phoneNumber, string $purpose, ?string $role = null): array
    {
        $phone = $this->normalizePhone($phoneNumber);
        if (!$phone) {
            return [
                'ok' => false,
                'message' => 'Enter a valid 10-digit Indian mobile number.',
            ];
        }

        $throttleKey = $this->throttleKey($phone, $purpose, $role);
        if (Cache::has($throttleKey)) {
            return [
                'ok' => false,
                'message' => 'Please wait before requesting another OTP.',
            ];
        }

        $otp = (string) random_int(100000, 999999);
        Cache::put(
            $this->otpKey($phone, $purpose, $role),
            [
                'otp' => $otp,
                'phone' => $phone,
                'purpose' => $purpose,
                'role' => $role,
            ],
            now()->addMinutes(self::OTP_TTL_MINUTES)
        );
        Cache::put($throttleKey, true, now()->addSeconds(self::RESEND_COOLDOWN_SECONDS));

        $normalizedForWa = $this->whatsApp->normalizeIndianPhone($phone);
        if (!$normalizedForWa) {
            return [
                'ok' => false,
                'message' => 'Invalid Indian mobile number format.',
            ];
        }

        $message = "Use OTP {$otp} to verify your mobile number for SimplyHiree. This OTP is valid for 10 minutes. Do not share it with anyone.";
        $result = $this->whatsApp->sendEventAlert(
            destination: $normalizedForWa,
            eventKey: 'auth.phone_otp',
            title: 'SimplyHiree OTP Verification',
            message: $message,
            metadata: [
                'user_name' => 'SimplyHiree User',
                // AUTH category template should receive only OTP code as variable.
                'template_params' => [$otp],
            ]
        );

        if (!($result['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => 'Unable to send OTP on WhatsApp right now. Please try again.',
                'details' => $result,
            ];
        }

        return [
            'ok' => true,
            'message' => 'OTP sent on WhatsApp.',
            'phone_number' => $phone,
            'expires_in_seconds' => self::OTP_TTL_MINUTES * 60,
        ];
    }

    public function verifyOtp(string $phoneNumber, string $otp, string $purpose, ?string $role = null): array
    {
        $phone = $this->normalizePhone($phoneNumber);
        if (!$phone) {
            return ['ok' => false, 'message' => 'Invalid mobile number.'];
        }

        $record = Cache::get($this->otpKey($phone, $purpose, $role));
        if (!is_array($record) || (string) ($record['otp'] ?? '') === '') {
            return ['ok' => false, 'message' => 'OTP expired or not found. Please request a new OTP.'];
        }

        if ((string) trim($otp) !== (string) $record['otp']) {
            return ['ok' => false, 'message' => 'Invalid OTP.'];
        }

        Cache::forget($this->otpKey($phone, $purpose, $role));

        $verificationToken = hash('sha256', Str::uuid()->toString() . '|' . $phone . '|' . $purpose);
        Cache::put(
            $this->verificationTokenKey($verificationToken),
            [
                'phone' => $phone,
                'purpose' => $purpose,
                'role' => $role,
            ],
            now()->addMinutes(self::VERIFY_TTL_MINUTES)
        );

        return [
            'ok' => true,
            'message' => 'Phone number verified.',
            'verification_token' => $verificationToken,
            'phone_number' => $phone,
        ];
    }

    public function consumeVerificationToken(
        string $phoneNumber,
        string $verificationToken,
        string $purpose,
        ?string $role = null
    ): bool {
        $phone = $this->normalizePhone($phoneNumber);
        if (!$phone || trim($verificationToken) === '') {
            return false;
        }

        $record = Cache::get($this->verificationTokenKey($verificationToken));
        if (!is_array($record)) {
            return false;
        }

        $recordPhone = (string) ($record['phone'] ?? '');
        $recordPurpose = (string) ($record['purpose'] ?? '');
        $recordRole = $record['role'] ?? null;

        if ($recordPhone !== $phone || $recordPurpose !== $purpose) {
            return false;
        }

        if ($role !== null && $recordRole !== null && (string) $recordRole !== (string) $role) {
            return false;
        }

        Cache::forget($this->verificationTokenKey($verificationToken));
        return true;
    }

    public function normalizePhone(?string $phoneNumber): ?string
    {
        if (!$phoneNumber) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phoneNumber) ?: '';

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }

        if (preg_match('/^[6-9][0-9]{9}$/', $digits)) {
            return $digits;
        }

        return null;
    }

    private function otpKey(string $phone, string $purpose, ?string $role): string
    {
        return 'phone_otp:' . $purpose . ':' . ($role ?: 'na') . ':' . $phone;
    }

    private function throttleKey(string $phone, string $purpose, ?string $role): string
    {
        return 'phone_otp_throttle:' . $purpose . ':' . ($role ?: 'na') . ':' . $phone;
    }

    private function verificationTokenKey(string $token): string
    {
        return 'phone_otp_verified:' . $token;
    }
}
