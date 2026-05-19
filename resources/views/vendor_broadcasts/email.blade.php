<!DOCTYPE html>
<html><head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; background:#eef2ff; padding:24px; margin:0; color:#0f172a;">
    <div style="max-width:640px; margin:0 auto; background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 4px 16px rgba(0,0,0,.06);">
        <div style="background:linear-gradient(135deg,#0443cd 0%,#312e81 100%); padding:24px 28px; color:#fff;">
            <div style="font-size:12px; letter-spacing:.18em; font-weight:700; color:#93c5fd; text-transform:uppercase;">SimplyHiree · Broadcast</div>
            <h1 style="margin:6px 0 0 0; font-size:22px; font-weight:800;">{{ $subject }}</h1>
        </div>
        <div style="padding:24px 28px;">
            <p style="margin:0 0 12px 0; font-size:14px; color:#475569;">Hi <strong>{{ $partner->name }}</strong>,</p>
            <div style="background:#f8fafc; border-left:4px solid #0443cd; padding:14px 16px; border-radius:6px; white-space:pre-wrap; line-height:1.6; font-size:14px; color:#0f172a;">{{ $body }}</div>
            <p style="margin:24px 0 0 0; font-size:12px; color:#94a3b8; text-align:center;">
                This message was sent from SimplyHiree to all connected vendor partners.<br>
                Log in to your dashboard for details:
                <a href="{{ url(route('partner.dashboard', [], false)) }}" style="color:#0443cd;">{{ url('/') }}/partner/dashboard</a>
            </p>
        </div>
    </div>
</body></html>
