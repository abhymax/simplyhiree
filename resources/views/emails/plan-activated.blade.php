<!DOCTYPE html><html><body style="margin:0;background:#f1f5f9;font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#0f172a">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px"><tr><td align="center">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#fff;border-radius:16px;overflow:hidden">
<tr><td style="background:linear-gradient(135deg,#4f46e5,#06b6d4);padding:22px 28px;color:#fff;font-weight:800;font-size:18px">SimplyHiree</td></tr>
<tr><td style="padding:30px 28px">
<h1 style="margin:0 0 10px;font-size:20px">Payment received 🎉</h1>
<p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#475569">Hi {{ $partner->name }}, your <strong>{{ $plan->name }}</strong> plan is now active until <strong>{{ $expiresAt->format('d M Y') }}</strong>.</p>
@if($payment)
<div style="background:#f8fafc;border-radius:12px;padding:14px 16px;font-size:13px;color:#475569">
<div>Base: ₹{{ number_format($payment->base_amount,2) }}</div>
<div>GST (18%): ₹{{ number_format($payment->gst_amount,2) }}</div>
<div style="font-weight:800;color:#0f172a;margin-top:4px">Total paid: ₹{{ number_format($payment->total_amount,2) }}</div>
@if($payment->razorpay_payment_id)<div style="margin-top:6px;color:#94a3b8">Payment ref: {{ $payment->razorpay_payment_id }}</div>@endif
</div>
@endif
<p style="margin:16px 0 0;font-size:13px;color:#64748b">Your new limits and features are available right away. We'll remind you before it expires.</p>
</td></tr></table></td></tr></table></body></html>
