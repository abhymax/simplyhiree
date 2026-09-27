<!DOCTYPE html><html><body style="margin:0;background:#f1f5f9;font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#0f172a">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px"><tr><td align="center">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#fff;border-radius:16px;overflow:hidden">
<tr><td style="background:linear-gradient(135deg,#f59e0b,#f97316);padding:22px 28px;color:#fff;font-weight:800;font-size:18px">SimplyHiree</td></tr>
<tr><td style="padding:30px 28px">
<h1 style="margin:0 0 10px;font-size:20px">Your {{ $partner->partner_plan }} plan expires in {{ $daysLeft }} day{{ $daysLeft==1?'':'s' }}</h1>
<p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#475569">Hi {{ $partner->name }}, your plan is valid until <strong>{{ optional($expiresAt)->format('d M Y') }}</strong>. Renew now to keep your submission limits, team seats and premium access without interruption.</p>
<p style="margin:0;font-size:13px;color:#64748b">Log in to your SimplyHiree partner dashboard → Plans &amp; Upgrade to renew.</p>
</td></tr></table></td></tr></table></body></html>
