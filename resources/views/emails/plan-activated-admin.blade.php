<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;color:#0f172a">
<h2>Partner plan upgrade</h2>
<p><strong>{{ $partner->name }}</strong> ({{ $partner->email }}) upgraded to <strong>{{ $plan->name }}</strong>.</p>
@if($payment)<p>Paid ₹{{ number_format($payment->total_amount,2) }} (base ₹{{ number_format($payment->base_amount,2) }} + GST ₹{{ number_format($payment->gst_amount,2) }}). Ref: {{ $payment->razorpay_payment_id }}</p>@endif
<p>Time: {{ now()->format('d M Y, h:i A') }}</p>
</body></html>
