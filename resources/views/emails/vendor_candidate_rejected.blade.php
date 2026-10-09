<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Candidate Application Update — SimplyHiree</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Inter', 'Segoe UI', Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f8fafc; padding: 40px 0; }
        .card { max-width: 600px; background: #ffffff; margin: 0 auto; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.05); }
        .header { background-color: #ffffff; padding: 28px 40px; text-align: center; border-bottom: 1px solid #e2e8f0; }
        .logo { font-size: 28px; font-weight: 800; color: #ffffff; letter-spacing: -0.02em; }
        .logo span { color: #fca5a5; }
        .tagline { color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 0.15em; font-weight: 700; margin-top: 4px; }
        .content { padding: 40px; }
        h1 { color: #b91c1c; font-size: 22px; font-weight: 800; margin: 0 0 16px 0; letter-spacing: -0.01em; }
        p { font-size: 15px; line-height: 1.6; color: #475569; margin: 0 0 16px 0; }
        .details-box { background: #fef2f2; border: 1px solid #fee2e2; padding: 24px; border-radius: 12px; margin: 28px 0; }
        .detail-title { font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #b91c1c; font-weight: 800; margin-bottom: 12px; }
        .detail-row { display: table; width: 100%; margin-bottom: 10px; font-size: 15px; }
        .detail-row:last-child { margin-bottom: 0; }
        .detail-label { display: table-cell; font-weight: 700; width: 140px; color: #b91c1c; }
        .detail-value { display: table-cell; color: #334155; }
        .status-badge { display: inline-block; padding: 6px 12px; font-size: 12px; font-weight: 700; background-color: #fee2e2; color: #b91c1c; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid #fecaca; }
        .footer { background: #f1f5f9; padding: 24px 40px; font-size: 12px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0; }
        .footer a { color: #ef4444; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header" style="background-color:#ffffff;padding:28px 40px;text-align:center;border-bottom:1px solid #e2e8f0;">
                <x-email-logo />
                <div class="tagline" style="color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:0.15em;font-weight:700;margin-top:4px;">Vendor Partner Network</div>
            </div>

            <div class="content">
                <h1>Application Status Update: Candidate Rejected</h1>
                <p>Hi {{ $partnerName }},</p>
                <p>We are writing to provide a status update on your candidate's application. Unfortunately, the client has decided to mark the candidate as <strong>Rejected</strong> at this stage.</p>
                
                <div class="details-box">
                    <div class="detail-title">Application Status Details</div>
                    <div class="detail-row">
                        <span class="detail-label">Application ID:</span>
                        <span class="detail-value"><strong>{{ $appCode }}</strong></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Candidate Name:</span>
                        <span class="detail-value">{{ $candidateName }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Job Title:</span>
                        <span class="detail-value">{{ $jobTitle }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Company Name:</span>
                        <span class="detail-value">{{ $companyName }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Current Status:</span>
                        <span class="detail-value"><span class="status-badge">Rejected</span></span>
                    </div>
                </div>

                <p>Although this specific application didn't go through, we encourage you to review other open requirements on the portal and match suitable candidates from your pool.</p>
                <p>Thank you for your active participation in our vendor program.</p>
                
                <p style="margin: 28px 0 0 0; color: #b91c1c;">
                    Best regards,<br>
                    <strong>SimplyHiree Partner Desk</strong>
                </p>
            </div>

            <div class="footer">
                <p style="margin: 0 0 8px 0;">This email is sent on behalf of SimplyHiree vendor program.</p>
                <p style="margin: 0;">
                    <a href="https://simplyhiree.com">www.simplyhiree.com</a> | 
                    <a href="https://simplyhiree.com/contact">Partner Support</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
