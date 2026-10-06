<!doctype html>
<html lang="en">
<body style="margin:0;background:#071632;color:#e5efff;font-family:Arial,sans-serif">
<div style="padding:32px 16px">
    <div style="max-width:620px;margin:auto;background:#102a62;border:1px solid #2b63bd;border-radius:16px;padding:32px">
        <div style="font-size:14px;font-weight:700;color:#62d9f3;letter-spacing:.08em">SIMPLYHIREE</div>
        <h1 style="margin:12px 0 16px;color:#fff;font-size:28px">Application received</h1>
        <p>Hello {{ $user->name }},</p>
        <p>Thank you for applying to the SimplyHiree Referral Partner Program. Our team will review your details and notify you by email when your account is approved.</p>
        <div style="margin:24px 0;padding:16px;background:#071f49;border-radius:10px;border:1px solid #29579a">
            <div style="font-size:12px;color:#9db9e8">YOUR REFERRAL CODE</div>
            <div style="margin-top:6px;font-size:20px;font-weight:700;color:#fff">{{ $profile->referral_code }}</div>
        </div>
        <p style="font-size:13px;color:#bdd3f5">Your referral link and dashboard access will become active only after Superadmin approval. You do not need to register again.</p>
    </div>
</div>
</body>
</html>
