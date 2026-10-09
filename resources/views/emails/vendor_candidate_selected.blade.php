<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Candidate Selected — SimplyHiree</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Inter', 'Segoe UI', Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f8fafc; padding: 40px 0; }
        .card { max-width: 600px; background: #ffffff; margin: 0 auto; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.05); }
        .header { background-color: #0f172a; background: linear-gradient(135deg, #10b981 0%, #0f172a 100%); padding: 32px 40px; text-align: center; }
        .logo { font-size: 28px; font-weight: 800; color: #ffffff; letter-spacing: -0.02em; }
        .logo span { color: #34d399; }
        .tagline { color: #94a3b8; font-size: 11px; text-transform: uppercase; letter-spacing: 0.15em; font-weight: 700; margin-top: 4px; }
        .content { padding: 40px; }
        h1 { color: #047857; font-size: 22px; font-weight: 800; margin: 0 0 16px 0; letter-spacing: -0.01em; }
        p { font-size: 15px; line-height: 1.6; color: #475569; margin: 0 0 16px 0; }
        .details-box { background: #ecfdf5; border: 1px solid #d1fae5; padding: 24px; border-radius: 12px; margin: 28px 0; }
        .detail-title { font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #047857; font-weight: 800; margin-bottom: 12px; }
        .detail-row { display: table; width: 100%; margin-bottom: 10px; font-size: 15px; }
        .detail-row:last-child { margin-bottom: 0; }
        .detail-label { display: table-cell; font-weight: 700; width: 140px; color: #047857; }
        .detail-value { display: table-cell; color: #334155; }
        .status-badge { display: inline-block; padding: 6px 12px; font-size: 12px; font-weight: 700; background-color: #d1fae5; color: #047857; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid #a7f3d0; }
        .footer { background: #f1f5f9; padding: 24px 40px; font-size: 12px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0; }
        .footer a { color: #10b981; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header" style="background-color:#0f172a;padding:32px 40px;text-align:center;">
                <div class="logo" style="font-size:28px;font-weight:800;color:#ffffff;letter-spacing:-0.02em;">Simply<span style="color:#38bdf8;">Hiree</span></div>
                <div class="tagline" style="color:#94a3b8;font-size:11px;text-transform:uppercase;letter-spacing:0.15em;font-weight:700;margin-top:4px;">Vendor Partner Network</div>
            </div>

            <div class="content">
                <h1>Congratulations! Your Candidate is Selected</h1>
                <p>Hi {{ $partnerName }},</p>
                <p>We have great news! Your candidate has been formally <strong>Selected</strong> by the client. Below are the details of the job selection:</p>
                
                <div class="details-box">
                    <div class="detail-title">Placement details</div>
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
                        <span class="detail-label">Expected Joining:</span>
                        <span class="detail-value">{{ $joiningDate }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Offered CTC:</span>
                        <span class="detail-value">{{ $ctc }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Current Status:</span>
                        <span class="detail-value"><span class="status-badge">Selected</span></span>
                    </div>
                </div>

                @if(!empty($notes))
                    <p style="font-weight: 700; margin-bottom: 8px; color: #1e293b;">Client Notes / Instructions:</p>
                    <p style="font-style: italic; background: #f8fafc; padding: 14px; border-radius: 8px; border-left: 4px solid #34d399; margin: 0 0 24px 0; color: #475569;">"{{ $notes }}"</p>
                @endif

                <p>Please connect with the candidate to ensure they complete their resignation process and prepare for onboarding on the specified date.</p>
                <p>We appreciate your efforts in driving successful placements!</p>
                
                <p style="margin: 28px 0 0 0; color: #047857;">
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
