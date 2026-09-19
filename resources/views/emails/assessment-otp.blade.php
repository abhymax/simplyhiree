<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f1f5f9;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px;">
        <tr><td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 6px 24px rgba(15,23,42,.08);">
                <tr><td style="background:linear-gradient(135deg,#4f46e5,#06b6d4);padding:22px 28px;color:#fff;font-weight:800;font-size:18px;">SimplyHiree</td></tr>
                <tr><td style="padding:30px 28px;">
                    <h1 style="margin:0 0 10px;font-size:20px;color:#0f172a;">Your verification code</h1>
                    <p style="margin:0 0 22px;font-size:14px;line-height:1.6;color:#475569;">
                        Use the code below to verify your email and begin your assessment
                        @if(!empty($job)) for <strong>{{ $job->title }}</strong>@endif.
                    </p>
                    <div style="text-align:center;margin:0 0 22px;">
                        <span style="display:inline-block;font-size:34px;font-weight:800;letter-spacing:10px;color:#4f46e5;background:#eef2ff;border-radius:12px;padding:16px 24px;">{{ $code }}</span>
                    </div>
                    <p style="margin:0 0 8px;font-size:13px;color:#64748b;">This code expires in {{ $ttl }} minutes.</p>
                    <p style="margin:0;font-size:13px;color:#64748b;">If you didn't request this, you can safely ignore this email. Never share this code with anyone.</p>
                </td></tr>
                <tr><td style="padding:16px 28px;background:#f8fafc;color:#94a3b8;font-size:11px;">© {{ date('Y') }} SimplyHiree. This is an automated message — please do not reply.</td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
