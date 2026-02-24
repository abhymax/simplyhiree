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
            'buttons' => [
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
            ],
            'carouselCards' => [],
            'location' => [],
            'attributes' => [],
            'paramsFallbackValue' => []
        ];

        if (isset($metadata['attributes']) && is_array($metadata['attributes']) && $metadata['attributes'] !== []) {
            $payload['attributes'] = $metadata['attributes'];
        }

        try {
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
