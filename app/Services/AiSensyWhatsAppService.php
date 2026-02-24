<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiSensyWhatsAppService
{
    public function enabled(): bool
    {
        return (bool) config('services.aisensy.enabled', false);
    }

    public function sendEventAlert(
        string $destination,
        string $eventKey,
        string $title,
        string $message,
        array $metadata = []
    ): array {
        if (!$this->enabled()) {
            return ['ok' => false, 'status' => 'skipped', 'error' => 'AISENSY_DISABLED'];
        }

        $apiKey = trim((string) config('services.aisensy.api_key', ''));
        $endpoint = trim((string) config('services.aisensy.endpoint', ''));
        $templates = (array) config('services.aisensy.templates', []);
        $defaultTemplate = (string) config('services.aisensy.default_template', '');
        $template = (string) ($templates[$eventKey] ?? $defaultTemplate);

        if ($apiKey === '' || $template === '') {
            return ['ok' => false, 'status' => 'skipped', 'error' => 'AISENSY_CONFIG_MISSING'];
        }

        $templateParams = $metadata['template_params'] ?? [];

        if (!is_array($templateParams)) {
            $templateParams = [(string) $templateParams];
        }

        $templateParams = array_values(array_map(static function ($v): string {
            return (string) $v;
        }, $templateParams));

        $payload = [
            'apiKey' => $apiKey,
            'campaignName' => $template,
            'destination' => $destination,
            'userName' => (string) ($metadata['user_name'] ?? 'SimplyHiree User'),
            'source' => 'simplyhiree-system',
            'templateParams' => $templateParams,
            'media' => [],
            'carouselCards' => [],
            'location' => [],
            'attributes' => [],
            'paramsFallbackValue' => [],
            'buttons' => [],
        ];

        // OTP auth campaign uses a URL button parameter in your AiSensy setup.
        if ($eventKey === 'auth.phone_otp') {
            $payload['buttons'] = [
                [
                    'type' => 'button',
                    'sub_type' => 'url',
                    'index' => 0,
                    'parameters' => [
                        [
                            'type' => 'text',
                            'text' => $templateParams[0] ?? ''
                        ]
                    ]
                ]
            ];
        }

        if (isset($metadata['attributes']) && is_array($metadata['attributes']) && $metadata['attributes'] !== []) {
            $payload['attributes'] = $metadata['attributes'];
        }

        try {
            $result = $this->dispatchPayload($endpoint, $payload, $eventKey);
            if (($result['ok'] ?? false) === true) {
                return $result;
            }

            // For non-OTP campaigns, retry with common template parameter shapes
            // when campaign expects params but metadata did not provide any.
            if ($eventKey !== 'auth.phone_otp' && empty($templateParams)) {
                $responseText = strtolower((string) ($result['response'] ?? ''));
                if (str_contains($responseText, 'template params does not match the campaign')) {
                    $fallbackSets = [
                        [trim($message)],
                        [trim($title), trim($message)],
                        [trim($title), trim($message), now()->format('d M Y, h:i A')],
                    ];

                    foreach ($fallbackSets as $fallbackParams) {
                        $retryPayload = $payload;
                        $retryPayload['templateParams'] = $fallbackParams;

                        $retry = $this->dispatchPayload($endpoint, $retryPayload, $eventKey);
                        if (($retry['ok'] ?? false) === true) {
                            return $retry;
                        }

                        $retryText = strtolower((string) ($retry['response'] ?? ''));
                        if (!str_contains($retryText, 'template params does not match the campaign')) {
                            return $retry;
                        }
                    }
                }
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('AiSensy exception', [
                'event_key' => $eventKey,
                'message' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function dispatchPayload(string $endpoint, array $payload, string $eventKey): array
    {
        $response = Http::timeout(20)
            ->acceptJson()
            ->asJson()
            ->post($endpoint, $payload);

        if ($response->successful()) {
            return [
                'ok' => true,
                'status' => 'sent',
                'response' => $response->json() ?: $response->body(),
            ];
        }

        Log::warning('AiSensy request failed', [
            'event_key' => $eventKey,
            'status' => $response->status(),
            'body' => $response->body(),
            'payload' => $payload,
        ]);

        return [
            'ok' => false,
            'status' => 'failed',
            'error' => 'HTTP_' . $response->status(),
            'response' => $response->body(),
        ];
    }

    public function normalizeIndianPhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?: '';

        if (strlen($digits) === 10 && preg_match('/^[6-9]/', $digits)) {
            return '91' . $digits;
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $trimmed = substr($digits, 1);
            if (preg_match('/^[6-9]/', $trimmed)) {
                return '91' . $trimmed;
            }
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return $digits;
        }

        return null;
    }
}
