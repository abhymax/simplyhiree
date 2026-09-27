<?php

namespace App\Services;

use App\Models\PartnerPayment;
use App\Models\PartnerPlan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class PlanService
{
    public function __construct(private AiSensyWhatsAppService $whatsapp)
    {
    }

    /**
     * Activate/renew a paid plan for a partner owner after a verified payment.
     * Extends the current period when renewing the same active plan, otherwise
     * starts a fresh 30-day (plan duration) window from now.
     */
    public function activatePaidPlan(User $owner, PartnerPlan $plan, ?PartnerPayment $payment = null): void
    {
        $duration = max(1, (int) ($plan->duration_days ?: 30));
        $now = now();

        $base = ($owner->plan_expires_at
            && $owner->plan_expires_at->isFuture()
            && strcasecmp((string) $owner->partner_plan, (string) $plan->name) === 0)
                ? $owner->plan_expires_at->copy()
                : $now->copy();

        $expires = $base->addDays($duration);

        $owner->forceFill([
            'partner_plan'            => $plan->name,
            'partner_tier'            => $plan->name,   // plan badge/tier (earned vendor_badge left untouched)
            'plan_started_at'         => $owner->plan_started_at && strcasecmp((string) $owner->partner_plan, (string) $plan->name) === 0 ? $owner->plan_started_at : $now,
            'plan_expires_at'         => $expires,
            'plan_expiry_reminded_on' => null,
        ])->save();

        $this->emailVendorActivated($owner, $plan, $expires, $payment);
        $this->notifyAdminActivated($owner, $plan, $payment);
        $this->waVendor($owner, "Your SimplyHiree {$plan->name} plan is active until " . $expires->format('d M Y') . ".");
    }

    /** Downgrade an expired partner back to Free. */
    public function downgradeToFree(User $owner): void
    {
        $owner->forceFill([
            'partner_plan'            => 'Free',
            'partner_tier'            => 'Free',
            'plan_started_at'         => null,
            'plan_expires_at'         => null,
            'plan_expiry_reminded_on' => null,
        ])->save();

        try {
            Mail::send('emails.plan-expired', ['partner' => $owner], function ($m) use ($owner) {
                $m->to($owner->email, $owner->name)->subject('Your SimplyHiree plan has expired — now on Free');
            });
        } catch (\Throwable $e) { report($e); }
        $this->waVendor($owner, 'Your SimplyHiree plan has expired and your account is back on the Free plan. Renew anytime from your dashboard.');
    }

    /** Daily reminder while within the last N days before expiry. */
    public function sendExpiryReminder(User $owner, int $daysLeft): void
    {
        try {
            Mail::send('emails.plan-expiry-reminder', [
                'partner' => $owner, 'daysLeft' => $daysLeft, 'expiresAt' => $owner->plan_expires_at,
            ], function ($m) use ($owner, $daysLeft) {
                $m->to($owner->email, $owner->name)
                  ->subject("Your {$owner->partner_plan} plan expires in {$daysLeft} day" . ($daysLeft == 1 ? '' : 's'));
            });
        } catch (\Throwable $e) { report($e); }
        $owner->forceFill(['plan_expiry_reminded_on' => now()->toDateString()])->save();
        $this->waVendor($owner, "Reminder: your SimplyHiree {$owner->partner_plan} plan expires in {$daysLeft} day" . ($daysLeft == 1 ? '' : 's') . ". Renew to keep your benefits.");
    }

    private function emailVendorActivated(User $owner, PartnerPlan $plan, Carbon $expires, ?PartnerPayment $payment): void
    {
        try {
            Mail::send('emails.plan-activated', [
                'partner' => $owner, 'plan' => $plan, 'expiresAt' => $expires, 'payment' => $payment,
            ], function ($m) use ($owner, $plan) {
                $m->to($owner->email, $owner->name)->subject("Payment received — your {$plan->name} plan is active");
            });
        } catch (\Throwable $e) { report($e); }
    }

    private function notifyAdminActivated(User $owner, PartnerPlan $plan, ?PartnerPayment $payment): void
    {
        try {
            $to = array_filter([
                config('mail.from.address'),
                'simplyhire1@gmail.com',
            ]);
            Mail::send('emails.plan-activated-admin', [
                'partner' => $owner, 'plan' => $plan, 'payment' => $payment,
            ], function ($m) use ($to, $owner, $plan) {
                $m->to($to)->subject("Partner upgrade: {$owner->name} → {$plan->name}");
            });
        } catch (\Throwable $e) { report($e); }
    }

    private function waVendor(User $owner, string $message): void
    {
        try {
            $phone = $this->whatsapp->normalizeIndianPhone($owner->mobile ?? $owner->phone ?? optional($owner->profile)->phone_number);
            if ($phone) {
                $this->whatsapp->sendEventAlert($phone, 'partner.plan_update', 'Plan update', $message, [
                    'user_name' => $owner->name, 'template_params' => [$owner->name, $message],
                ]);
            }
        } catch (\Throwable $e) { report($e); }
    }
}
