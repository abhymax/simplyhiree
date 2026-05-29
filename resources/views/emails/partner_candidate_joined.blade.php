<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Placement Successful! — SimplyHiree</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Inter', 'Segoe UI', Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f8fafc; padding: 40px 0; }
        .card { max-width: 600px; background: #ffffff; margin: 0 auto; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.05); }
        .header { background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%); padding: 32px 40px; text-align: center; }
        .logo { font-size: 28px; font-weight: 800; color: #ffffff; letter-spacing: -0.02em; }
        .logo span { color: #60a5fa; }
        .tagline { color: #94a3b8; font-size: 11px; text-transform: uppercase; letter-spacing: 0.15em; font-weight: 700; margin-top: 4px; }
        .content { padding: 40px; }
        h1 { color: #1e3a8a; font-size: 22px; font-weight: 800; margin: 0 0 16px 0; letter-spacing: -0.01em; }
        p { font-size: 15px; line-height: 1.6; color: #475569; margin: 0 0 16px 0; }
        .details-box { background: #f0f7ff; border: 1px solid #e0f2fe; padding: 24px; border-radius: 12px; margin: 28px 0; }
        .detail-title { font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #0284c7; font-weight: 800; margin-bottom: 12px; }
        .detail-row { display: table; width: 100%; margin-bottom: 10px; font-size: 15px; }
        .detail-row:last-child { margin-bottom: 0; }
        .detail-label { display: table-cell; font-weight: 700; width: 120px; color: #1e3a8a; }
        .detail-value { display: table-cell; color: #334155; }
        .action-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; margin: 28px 0; }
        .action-title { font-size: 14px; font-weight: 700; color: #1e3a8a; margin-bottom: 4px; }
        .action-text { font-size: 14px; color: #475569; margin: 0 0 12px 0; }
        .btn { display: inline-block; background-color: #2563eb; color: #ffffff !important; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; text-align: center; transition: background-color 0.2s; }
        .btn:hover { background-color: #1d4ed8; }
        .footer { background: #f1f5f9; padding: 24px 40px; font-size: 12px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0; }
        .footer a { color: #2563eb; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <!-- Header with stylized logo and brand tagline -->
            <div class="header">
                <div class="logo">Simply<span>Hiree</span></div>
                <div class="tagline">Connecting Talent · Empowering Careers</div>
            </div>

            <!-- Content Area -->
            <div class="content">
                <h1>Successful Placement Alert! 🎉</h1>
                <p>Dear {{ $partnerName }},</p>
                <p>We are thrilled to inform you that your candidate, <strong>{{ $name }}</strong>, has successfully joined the company!</p>
                <p>This is a significant milestone, and we congratulate you on a highly successful placement. Below are the finalized details for this joining:</p>
                
                <!-- Placement Details -->
                <div class="details-box">
                    <div class="detail-title">Placement Details</div>
                    <div class="detail-row">
                        <span class="detail-label">Candidate Name:</span>
                        <span class="detail-value"><strong>{{ $name }}</strong></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Hiring Company:</span>
                        <span class="detail-value">{{ $company }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Sourced Role:</span>
                        <span class="detail-value">{{ $role }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Joining Date:</span>
                        <span class="detail-value">{{ $joining_date }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Final CTC:</span>
                        <span class="detail-value">{{ $ctc }}</span>
                    </div>
                </div>

                <!-- Next Steps Callout -->
                <div class="action-box">
                    <div class="action-title">Post-Placement Actions</div>
                    <p class="action-text">
                        Please log in to your SimplyHiree Partner Portal to review this placement, track the commercial billing status, and access candidate onboarding updates.
                    </p>
                    <a href="https://simplyhiree.com/partner/login" class="btn" target="_blank">Access Partner Portal</a>
                </div>

                <p>Thank you for partnering with SimplyHiree. We look forward to achieving many more successful placements together!</p>
                
                <p style="margin: 28px 0 0 0; color: #1e3a8a;">
                    Best regards,<br>
                    <strong>SimplyHiree Team</strong>
                </p>
            </div>

            <!-- Styled Footer -->
            <div class="footer">
                <p style="margin: 0 0 8px 0;">This email is an automated confirmation sent on behalf of the hiring team via SimplyHiree.</p>
                <p style="margin: 0;">
                    <a href="https://simplyhiree.com">www.simplyhiree.com</a> | 
                    <a href="https://simplyhiree.com/contact">Support</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
