<!doctype html>
<html lang="en">
<body style="margin:0;background:#071632;color:#e5efff;font-family:Arial,sans-serif">
<div style="padding:32px 16px">
    <div style="max-width:620px;margin:auto;background:#102a62;border:1px solid #2b63bd;border-radius:16px;padding:32px">
        <div style="font-size:14px;font-weight:700;color:#62d9f3;letter-spacing:.08em">SIMPLYHIREE</div>
        <h1 style="margin:12px 0 16px;color:#fff;font-size:28px">Welcome to Referral Partners</h1>
        <p>Hello {{ $user->name }},</p>
        <p>Your Referral Partner application has been approved. Your referral link is active and your dashboard is ready.</p>
        <p style="margin:24px 0">
            <a href="{{ $dashboardUrl }}" style="display:inline-block;background:#22c7e8;color:#071632;padding:12px 18px;border-radius:8px;font-weight:bold;text-decoration:none">Open Referral Dashboard</a>
        </p>
        <div style="margin:24px 0;padding:16px;background:#071f49;border-radius:10px;border:1px solid #29579a">
            <div style="font-size:12px;color:#9db9e8">YOUR REFERRAL LINK</div>
            <a href="{{ $referralUrl }}" style="display:block;margin-top:6px;color:#8ee7ff;word-break:break-all">{{ $referralUrl }}</a>
        </div>
        <p>Sign in with <strong style="color:#fff">{{ $user->email }}</strong> and the password you created during registration.</p>
        <p style="font-size:13px;color:#bdd3f5">Forgot the password? <a href="{{ $passwordResetUrl }}" style="color:#8ee7ff">Create a new password securely</a>.</p>
    </div>
</div>
</body>
</html>
