<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout · {{ $plan->name }} plan · SimplyHiree</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;background:linear-gradient(135deg,#0f172a,#1e1b4b 60%,#312e81);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;color:#e2e8f0;display:flex;align-items:center;justify-content:center;padding:24px 16px}
        .card{width:100%;max-width:440px;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.18);border-radius:20px;padding:30px 26px;box-shadow:0 24px 60px rgba(0,0,0,.45)}
        .brand{display:flex;align-items:center;gap:10px;margin-bottom:20px}.logo{width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#4f46e5,#06b6d4);display:flex;align-items:center;justify-content:center;font-weight:800;color:#fff}
        h1{font-size:1.2rem;margin:0 0 4px;color:#fff}.sub{color:#a5b4fc;font-size:.85rem;margin:0 0 20px}
        .row{display:flex;justify-content:space-between;padding:9px 0;font-size:.9rem;color:#cbd5e1;border-bottom:1px solid rgba(148,163,184,.12)}
        .row.total{border-bottom:0;margin-top:6px;font-size:1.05rem;font-weight:800;color:#fff}
        .btn{display:block;width:100%;margin-top:22px;padding:14px;border:none;border-radius:12px;font-size:1rem;font-weight:800;color:#fff;background:linear-gradient(135deg,#4f46e5,#06b6d4);cursor:pointer}
        .btn:hover{filter:brightness(1.08)}.muted{color:#94a3b8;font-size:.78rem;text-align:center;margin-top:16px;line-height:1.5}
        a.back{color:#67e8f9;font-size:.85rem;text-decoration:none}
    </style>
</head>
<body>
<div class="card">
    <div class="brand"><div class="logo">SH</div><span style="font-weight:800;color:#fff">SimplyHiree</span></div>
    <h1>Upgrade to {{ $plan->name }}</h1>
    <p class="sub">{{ $plan->duration_days ?? 30 }}-day plan · billed once</p>

    <div class="row"><span>{{ $plan->name }} plan (base)</span><span>₹{{ number_format($base, 2) }}</span></div>
    <div class="row"><span>GST (18%)</span><span>₹{{ number_format($gst, 2) }}</span></div>
    <div class="row total"><span>Total payable</span><span>₹{{ number_format($total, 2) }}</span></div>

    <button id="payBtn" class="btn">Pay ₹{{ number_format($total, 2) }}</button>
    <p class="muted">Secure payment via Razorpay. Your plan activates automatically on success.</p>
    <p style="text-align:center;margin-top:14px"><a class="back" href="{{ route('partner.upgrade') }}">← Back to plans</a></p>

    <form id="verifyForm" method="POST" action="{{ route('partner.billing.verify') }}" style="display:none">
        @csrf
        <input type="hidden" name="razorpay_order_id" id="f_order">
        <input type="hidden" name="razorpay_payment_id" id="f_payment">
        <input type="hidden" name="razorpay_signature" id="f_signature">
    </form>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    var options = {
        key: @json($keyId),
        order_id: @json($order['id']),
        amount: @json($order['amount']),
        currency: "INR",
        name: "SimplyHiree",
        description: @json($plan->name . ' plan (30 days)'),
        prefill: { name: @json($owner->name), email: @json($owner->email) },
        theme: { color: "#4f46e5" },
        handler: function (response) {
            document.getElementById('f_order').value = response.razorpay_order_id;
            document.getElementById('f_payment').value = response.razorpay_payment_id;
            document.getElementById('f_signature').value = response.razorpay_signature;
            document.getElementById('verifyForm').submit();
        },
        modal: { ondismiss: function () { /* stay on page */ } }
    };
    var rzp = new Razorpay(options);
    document.getElementById('payBtn').addEventListener('click', function () { rzp.open(); });
    // auto-open on load
    window.addEventListener('load', function(){ rzp.open(); });
</script>
</body>
</html>
