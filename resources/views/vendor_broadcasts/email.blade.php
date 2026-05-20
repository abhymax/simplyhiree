<!DOCTYPE html>
<html><head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; background:#eef2ff; padding:24px; margin:0; color:#0f172a;">
    <div style="max-width:640px; margin:0 auto; background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 4px 16px rgba(0,0,0,.06);">
        <div style="background:linear-gradient(135deg,#0443cd 0%,#312e81 100%); padding:24px 28px; color:#fff;">
            <h1 style="margin:6px 0 0 0; font-size:22px; font-weight:800;">{{ $subject }}</h1>
        </div>
        <div style="padding:24px 28px;">
            <p style="margin:0 0 12px 0; font-size:14px; color:#475569;">Hi <strong>{{ $partner->name }}</strong>,</p>
            <div style="background:#f8fafc; border-left:4px solid #0443cd; padding:14px 16px; border-radius:6px; white-space:pre-wrap; line-height:1.6; font-size:14px; color:#0f172a;">{{ $body }}</div>
            <div style="margin:30px 0 10px 0; text-align:center;">
                <p style="margin-bottom:16px; font-size:13px; color:#64748b;">Please log in to your dashboard to view more details and take action.</p>
                <a href="{{ url(route('partner.dashboard', [], false)) }}" style="display:inline-block; padding:10px 24px; background-color:#0443cd; color:#ffffff; text-decoration:none; border-radius:6px; font-weight:600; font-size:14px;">Go to Dashboard</a>
            </div>
        </div>
    </div>
</body></html>
