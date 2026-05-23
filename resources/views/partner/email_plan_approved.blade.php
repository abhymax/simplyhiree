<!DOCTYPE html>
<html><head><meta charset="utf-8"></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; background:#eef2ff; padding:24px; margin:0; color:#0f172a;">
    <div style="max-width:640px; margin:0 auto; background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 4px 18px rgba(0,0,0,.06);">

        <div style="background:linear-gradient(135deg,#0443cd 0%,#312e81 100%); padding:32px 28px; color:#fff; text-align:center;">
            <div style="display:inline-flex; align-items:center; justify-content:center; width:64px; height:64px; border-radius:50%; background:rgba(16,185,129,.20); margin-bottom:12px;">
                <span style="font-size:32px;">🎉</span>
            </div>
            <div style="font-size:12px; letter-spacing:.18em; font-weight:700; color:#a5b4fc; text-transform:uppercase; margin-bottom:4px;">Plan Upgraded</div>
            <h1 style="margin:0; font-size:24px; font-weight:800;">Welcome to {{ $newPlan }}</h1>
        </div>

        <div style="padding:28px 32px;">
            <p style="margin:0 0 18px 0; font-size:15px; color:#475569;">Hi <strong style="color:#0f172a;">{{ $partner->name }}</strong>,</p>

            <p style="margin:0 0 18px 0; font-size:14px; line-height:1.6; color:#0f172a;">
                Great news — your plan change request has been <strong style="color:#10b981;">approved</strong> and your account is now on the <strong>{{ $newPlan }}</strong> plan. Enjoy your new benefits.
            </p>

            <table style="width:100%; border-collapse:collapse; font-size:14px; margin:18px 0;">
                <tr><td style="padding:8px 0; color:#64748b; width:140px;"><strong>Previous Plan</strong></td><td style="color:#0f172a;">{{ $oldPlan }}</td></tr>
                <tr><td style="padding:8px 0; color:#64748b;"><strong>New Plan</strong></td><td style="color:#0443cd; font-weight:800;">{{ $newPlan }}</td></tr>
                @if($monthlyCap)
                    <tr><td style="padding:8px 0; color:#64748b;"><strong>Monthly Submissions</strong></td><td style="color:#0f172a;">{{ $monthlyCap }}</td></tr>
                @else
                    <tr><td style="padding:8px 0; color:#64748b;"><strong>Monthly Submissions</strong></td><td style="color:#10b981; font-weight:700;">Unlimited</td></tr>
                @endif
                <tr><td style="padding:8px 0; color:#64748b;"><strong>Max Sub-Recruiters</strong></td><td style="color:#0f172a;">{{ $maxTeam }}</td></tr>
                <tr><td style="padding:8px 0; color:#64748b;"><strong>Premium Visibility</strong></td><td style="color:#0f172a;">{{ $premiumJobs ? '✓ Enabled' : '— Not included' }}</td></tr>
                <tr><td style="padding:8px 0; color:#64748b;"><strong>Activated On</strong></td><td style="color:#0f172a;">{{ now()->format('d M Y, h:i A') }}</td></tr>
            </table>

            @if(!empty($adminNotes))
                <div style="background:#f8fafc; border-left:4px solid #0443cd; padding:14px 16px; border-radius:6px; font-size:13px; color:#0f172a; margin-bottom:18px;">
                    <strong style="color:#0443cd;">A note from your account manager:</strong><br>
                    <span style="white-space:pre-wrap;">{{ $adminNotes }}</span>
                </div>
            @endif

            <div style="text-align:center; margin:28px 0 10px;">
                <a href="{{ url(route('partner.dashboard', [], false)) }}"
                   style="background:#0443cd; color:#fff; padding:12px 28px; border-radius:10px; font-weight:700; text-decoration:none; display:inline-block; font-size:14px;">
                    Open Partner Dashboard →
                </a>
            </div>

            <p style="margin:24px 0 0 0; font-size:12px; color:#94a3b8; text-align:center; line-height:1.6;">
                Need help? Reply to this email, or open a support ticket from your dashboard.<br>
                Welcome to the {{ $newPlan }} tier — we're glad to have you here.
            </p>
        </div>
    </div>
</body></html>
