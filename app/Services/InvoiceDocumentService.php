<?php

namespace App\Services;

use App\Models\ClientProfile;
use App\Models\JobApplication;

class InvoiceDocumentService
{
    public function build(JobApplication $application, array $snapshot): array
    {
        $issuer = config('invoice.issuer');
        $clientProfile = $application->job?->user?->clientProfile;
        $taxableValue = round((float) ($snapshot['invoice_amount'] ?? 0), 2);
        $rate = round((float) ($snapshot['gst_rate'] ?? 0), 2);
        $tax = $this->taxBreakdown($taxableValue, $rate, $issuer['state'] ?? '', $clientProfile);
        $financialYear = $this->financialYear(now()->year, now()->month);

        return [
            'issuer' => $issuer,
            'clientProfile' => $clientProfile,
            'application' => $application,
            'snapshot' => $snapshot,
            'sac' => config('invoice.default_sac'),
            'serviceDescription' => config('invoice.default_service_description'),
            'paymentTermsDays' => (int) config('invoice.payment_terms_days', 15),
            'invoiceNumber' => sprintf('%s/%s/%06d', config('invoice.number_prefix', 'SH/INV'), $financialYear, $application->id),
            'invoiceDate' => $application->invoice_generated_at ?? now(),
            'dueDate' => ($application->invoice_generated_at ?? now())->copy()->addDays((int) config('invoice.payment_terms_days', 15)),
            'tax' => $tax,
            'isProvisional' => blank($issuer['gstin']) || blank($issuer['pan']),
            'stampDataUri' => $this->stampDataUri($issuer['stamp_path'] ?? null),
        ];
    }

    private function stampDataUri(?string $path): ?string
    {
        if (!is_string($path) || !is_file($path) || !is_readable($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        $mimeType = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mimeType.';base64,'.base64_encode($contents);
    }

    private function taxBreakdown(float $taxableValue, float $rate, string $issuerState, ?ClientProfile $recipient): array
    {
        $recipientState = trim((string) ($recipient?->state ?? ''));
        $sameState = $recipientState !== '' && $this->normaliseState($recipientState) === $this->normaliseState($issuerState);
        $intraState = $rate > 0 && $sameState;
        $interState = $rate > 0 && !$sameState;
        $cgstRate = $intraState ? $rate / 2 : 0;
        $sgstRate = $intraState ? $rate / 2 : 0;
        $igstRate = $interState ? $rate : 0;
        $cgstAmount = round($taxableValue * $cgstRate / 100, 2);
        $sgstAmount = round($taxableValue * $sgstRate / 100, 2);
        $igstAmount = round($taxableValue * $igstRate / 100, 2);

        return [
            'recipient_state' => $recipientState ?: 'Not provided',
            'recipient_state_code' => $this->stateCode($recipientState),
            'type' => $rate <= 0 ? 'not_applicable' : ($intraState ? 'intra_state' : 'inter_state'),
            'cgst_rate' => $cgstRate,
            'cgst_amount' => $cgstAmount,
            'sgst_rate' => $sgstRate,
            'sgst_amount' => $sgstAmount,
            'igst_rate' => $igstRate,
            'igst_amount' => $igstAmount,
            'total_tax' => round($cgstAmount + $sgstAmount + $igstAmount, 2),
            'grand_total' => round($taxableValue + $cgstAmount + $sgstAmount + $igstAmount, 2),
        ];
    }

    private function financialYear(int $year, int $month): string
    {
        return $month >= 4 ? $year.'-'.substr((string) ($year + 1), -2) : ($year - 1).'-'.substr((string) $year, -2);
    }

    private function normaliseState(string $state): string
    {
        $state = strtoupper(trim(preg_replace('/\s+/', ' ', $state)));
        return match ($state) {
            'UP', 'U.P.', 'UTTAR PRADESH' => 'UTTAR PRADESH',
            default => $state,
        };
    }

    private function stateCode(string $state): string
    {
        return match ($this->normaliseState($state)) {
            'UTTAR PRADESH' => '09',
            'DELHI', 'NCT OF DELHI' => '07',
            'HARYANA' => '06',
            'MAHARASHTRA' => '27',
            'KARNATAKA' => '29',
            'TAMIL NADU' => '33',
            'WEST BENGAL' => '19',
            'TELANGANA' => '36',
            'ANDHRA PRADESH' => '37',
            'GUJARAT' => '24',
            'RAJASTHAN' => '08',
            'MADHYA PRADESH' => '23',
            'BIHAR' => '10',
            'PUNJAB' => '03',
            'KERALA' => '32',
            default => '—',
        };
    }
}
