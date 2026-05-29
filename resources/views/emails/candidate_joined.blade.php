<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to the Team! — SimplyHiree</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Inter', 'Segoe UI', Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f8fafc; padding: 40px 0; }
        .card { max-width: 600px; background: #ffffff; margin: 0 auto; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.05); }
        .header { background: linear-gradient(135deg, #10b981 0%, #064e3b 100%); padding: 32px 40px; text-align: center; }
        .logo { font-size: 28px; font-weight: 800; color: #ffffff; letter-spacing: -0.02em; }
        .logo span { color: #a7f3d0; }
        .tagline { color: #d1fae5; font-size: 11px; text-transform: uppercase; letter-spacing: 0.15em; font-weight: 700; margin-top: 4px; }
        .content { padding: 40px; }
        h1 { color: #065f46; font-size: 22px; font-weight: 800; margin: 0 0 16px 0; letter-spacing: -0.01em; }
        p { font-size: 15px; line-height: 1.6; color: #475569; margin: 0 0 16px 0; }
        .details-box { background: #f0fdf4; border: 1px solid #dcfce7; padding: 24px; border-radius: 12px; margin: 28px 0; }
        .detail-title { font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #16a34a; font-weight: 800; margin-bottom: 12px; }
        .detail-row { display: table; width: 100%; margin-bottom: 10px; font-size: 15px; }
        .detail-row:last-child { margin-bottom: 0; }
        .detail-label { display: table-cell; font-weight: 700; width: 120px; color: #065f46; }
        .detail-value { display: table-cell; color: #334155; }
        .partner-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; margin: 28px 0; }
        .partner-title { font-size: 14px; font-weight: 700; color: #065f46; margin-bottom: 4px; }
        .partner-text { font-size: 14px; color: #475569; margin: 0; }
        .footer { background: #f1f5f9; padding: 24px 40px; font-size: 12px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0; }
        .footer a { color: #10b981; text-decoration: none; font-weight: 600; }
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
                <h1>Welcome to the Team! 🎉</h1>
                <p>Hi {{ $name }},</p>
                <p>We are absolutely thrilled to welcome you! This email confirms that you have officially joined the company. We wish you an amazing journey ahead.</p>
                
                <!-- Joining Details -->
                <div class="details-box">
                    <div class="detail-title">Job Details</div>
                    <div class="detail-row">
                        <span class="detail-label">Company:</span>
                        <span class="detail-value">{{ $company }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Role:</span>
                        <span class="detail-value">{{ $role }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Joining Date:</span>
                        <span class="detail-value">{{ $joining_date }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">CTC:</span>
                        <span class="detail-value">{{ $ctc }}</span>
                    </div>
                </div>

                <!-- Sourcing Partner Callout -->
                @if(!empty($partnerName))
                    <div class="partner-box">
                        <div class="partner-title">Sourcing Partner Coordinator</div>
                        <p class="partner-text">
                            For onboarding documents, training logs, or coordination, please contact your partner coordinator <strong>{{ $partnerName }}</strong>.
                        </p>
                    </div>
                @endif

                <p>Please log in to your SimplyHiree candidate portal to verify your onboarding dashboard, fill in your background check forms, and access your training tools.</p>
                <p>Congratulations once again, and we look forward to seeing your accomplishments!</p>
                
                <p style="margin: 28px 0 0 0; color: #065f46;">
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
