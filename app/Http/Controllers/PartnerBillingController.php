<?php

namespace App\Http\Controllers;

use App\Models\PartnerPayment;
use App\Models\PartnerPlan;
use App\Models\User;
use App\Services\PlanService;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PartnerBillingController extends Controller
{
    private const GST_RATE = 0.18;

    public function __construct(private RazorpayService $razor, private PlanService $plans)
    {
    }

    /** Build the Razorpay order + checkout page for a plan. */
    public function checkout(Request $request, string $plan)
    {
        $owner = Auth::user();
        if (!$owner->isPartnerOwner()) {
            return redirect()->route('partner.upgrade')->with('error', 'Only the partner account owner can upgrade the plan.');
        }

        $planRow = PartnerPlan::where('name', $plan)->where('is_purchasable', true)->first();
        if (!$planRow) {
            return redirect()->route('partner.upgrade')->with('error', 'That plan is not available for online purchase.');
        }
        if (!$this->razor->enabled()) {
            return redirect()->route('partner.upgrade')->with('error', 'Online payments are not configured yet. Please contact SimplyHiree.');
        }

        $base  = round((float) $planRow->price, 2);
        $gst   = round($base * self::GST_RATE, 2);
        $total = round($base + $gst, 2);
        $paise = (int) round($total * 100);

        $order = $this->razor->createOrder($paise, 'plan_' . $owner->id . '_' . time(), [
            'partner_id' => (string) $owner->id,
            'plan'       => $planRow->name,
        ]);
        if (!$order || empty($order['id'])) {
            return redirect()->route('partner.upgrade')->with('error', 'Could not start the payment. Please try again.');
        }

        $payment = PartnerPayment::create([
            'partner_id'        => $owner->id,
            'plan_name'         => $planRow->name,
            'duration_days'     => (int) ($planRow->duration_days ?: 30),
            'base_amount'       => $base,
            'gst_amount'        => $gst,
            'total_amount'      => $total,
            'currency'          => 'INR',
            'razorpay_order_id' => $order['id'],
            'status'            => 'created',
        ]);

        return view('partner.billing.checkout', [
            'plan'    => $planRow,
            'order'   => $order,
            'payment' => $payment,
            'owner'   => $owner,
            'keyId'   => $this->razor->keyId(),
            'base'    => $base,
            'gst'     => $gst,
            'total'   => $total,
        ]);
    }

    /** Browser callback after checkout — verify signature and activate. */
    public function verify(Request $request)
    {
        $data = $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        $ok = $this->razor->verifyPaymentSignature(
            $data['razorpay_order_id'], $data['razorpay_payment_id'], $data['razorpay_signature']
        );
        if (!$ok) {
            return redirect()->route('partner.upgrade')->with('error', 'Payment could not be verified. If money was deducted it will auto-reconcile shortly.');
        }

        $payment = PartnerPayment::where('razorpay_order_id', $data['razorpay_order_id'])->first();
        if (!$payment) {
            return redirect()->route('partner.upgrade')->with('error', 'Payment record not found.');
        }

        $this->fulfil($payment, $data['razorpay_payment_id'], $data['razorpay_signature']);

        return redirect()->route('partner.upgrade')->with('success', 'Payment successful! Your ' . $payment->plan_name . ' plan is now active.');
    }

    /** Razorpay server-to-server webhook (source of truth). */
    public function webhook(Request $request)
    {
        $raw = $request->getContent();
        $sig = $request->header('X-Razorpay-Signature', '');
        if (!$this->razor->verifyWebhookSignature($raw, (string) $sig)) {
            return response()->json(['ok' => false], 400);
        }

        $payload = json_decode($raw, true) ?: [];
        $event = $payload['event'] ?? '';

        if (in_array($event, ['payment.captured', 'order.paid'], true)) {
            $entity   = $payload['payload']['payment']['entity'] ?? [];
            $orderId  = $entity['order_id'] ?? ($payload['payload']['order']['entity']['id'] ?? null);
            $payId    = $entity['id'] ?? null;
            if ($orderId) {
                $payment = PartnerPayment::where('razorpay_order_id', $orderId)->first();
                if ($payment) {
                    $this->fulfil($payment, $payId, null);
                }
            }
        }

        return response()->json(['ok' => true]);
    }

    /** Idempotently mark paid + activate the plan. */
    private function fulfil(PartnerPayment $payment, ?string $razorpayPaymentId, ?string $signature): void
    {
        DB::transaction(function () use ($payment, $razorpayPaymentId, $signature) {
            $payment = PartnerPayment::whereKey($payment->id)->lockForUpdate()->first();
            if ($payment->status === 'paid') {
                return; // already fulfilled
            }
            $payment->update([
                'status'              => 'paid',
                'razorpay_payment_id' => $razorpayPaymentId ?: $payment->razorpay_payment_id,
                'razorpay_signature'  => $signature ?: $payment->razorpay_signature,
                'paid_at'             => now(),
            ]);

            $owner = User::find($payment->partner_id);
            $plan  = PartnerPlan::where('name', $payment->plan_name)->first();
            if ($owner && $plan) {
                app(PlanService::class)->activatePaidPlan($owner, $plan, $payment);
            }
        });
    }
}
