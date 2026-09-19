<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f1f5f9;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px;">
        <tr><td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 6px 24px rgba(15,23,42,.08);">
                <tr><td style="background:linear-gradient(135deg,#4f46e5,#06b6d4);padding:22px 28px;color:#fff;font-weight:800;font-size:18px;">SimplyHiree</td></tr>
                <tr><td style="padding:30px 28px;">
                    <h1 style="margin:0 0 10px;font-size:20px;color:#0f172a;">Complete your assessment</h1>
                    <p style="margin:0 0 18px;font-size:14px;line-height:1.6;color:#475569;">
                        Hi {{ $session->candidate_name ?: 'there' }}, you've been lined up
                        @if(!empty($job)) for <strong>{{ $job->title }}</strong>@endif.
                        To move forward, please complete a short assessment. Click below to verify your email and begin.
                    </p>
                    <div style="text-align:center;margin:0 0 22px;">
                        <a href="{{ $url }}" style="display:inline-block;background:linear-gradient(135deg,#4f46e5,#06b6d4);color:#fff;text-decoration:none;font-weight:800;font-size:15px;padding:14px 28px;border-radius:12px;">Start assessment</a>
                    </div>
                    <p style="margin:0 0 8px;font-size:12px;color:#64748b;">Or paste this link into your browser:</p>
                    <p style="margin:0 0 16px;font-size:12px;word-break:break-all;color:#4f46e5;">{{ $url }}</p>
                    <p style="margin:0;font-size:13px;color:#64748b;">This link is personal to you — please don't share it.</p>
                </td></tr>
                <tr><td style="padding:16px 28px;background:#f8fafc;color:#94a3b8;font-size:11px;">© {{ date('Y') }} SimplyHiree. This is an automated message — please do not reply.</td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
