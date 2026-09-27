<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Minimal Razorpay integration using direct REST calls + HMAC signature
 * verification — no composer package required.
 */
class RazorpayService
{
    public function enabled(): bool
    {
        return $this->keyId() !== '' && $this->keySecret() !== '';
    }

    public function keyId(): string
    {
        return trim((string) config('services.razorpay.key_id', ''));
    }

    private function keySecret(): string
    {
        return trim((string) config('services.razorpay.key_secret', ''));
    }

    private function webhookSecret(): string
    {
        return trim((string) config('services.razorpay.webhook_secret', ''));
    }

    /**
     * Create an order. $amountPaise is the total in paise (integer).
     * Returns the Razorpay order array or null on failure.
     */
    public function createOrder(int $amountPaise, string $receipt, array $notes = []): ?array
    {
        if (!$this->enabled()) {
            return null;
        }
        try {
            $resp = Http::withBasicAuth($this->keyId(), $this->keySecret())
                ->asJson()->acceptJson()->timeout(20)
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount'          => $amountPaise,
                    'currency'        => 'INR',
                    'receipt'         => $receipt,
                    'payment_capture' => 1,
                    'notes'           => $notes,
                ]);
            if ($resp->successful()) {
                return $resp->json();
            }
            Log::warning('Razorpay createOrder failed', ['status' => $resp->status(), 'body' => $resp->body()]);
        } catch (\Throwable $e) {
            Log::error('Razorpay createOrder exception: ' . $e->getMessage());
        }
        return null;
    }

    /** Verify the checkout callback signature: hmac_sha256(order_id|payment_id, key_secret). */
    public function verifyPaymentSignature(string $orderId, string $paymentId, string $signature): bool
    {
        if ($signature === '' || !$this->enabled()) {
            return false;
        }
        $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret());
        return hash_equals($expected, $signature);
    }

    /** Verify a webhook payload signature against the webhook secret. */
    public function verifyWebhookSignature(string $rawBody, string $signature): bool
    {
        $secret = $this->webhookSecret();
        if ($secret === '' || $signature === '') {
            return false;
        }
        $expected = hash_hmac('sha256', $rawBody, $secret);
        return hash_equals($expected, $signature);
    }
}
