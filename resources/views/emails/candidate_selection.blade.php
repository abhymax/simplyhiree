<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Selection Confirmation — SimplyHiree</title>
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
        .alert-box { background: #fcf8e3; border-left: 4px solid #f0ad4e; padding: 16px; border-radius: 8px; margin: 24px 0; font-size: 14px; line-height: 1.5; color: #8a6d3b; }
        .partner-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; margin: 28px 0; }
        .partner-title { font-size: 14px; font-weight: 700; color: #1e3a8a; margin-bottom: 4px; }
        .partner-text { font-size: 14px; color: #475569; margin: 0; }
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
                <h1>{{ $isUpdate ? 'Revised Selection Details' : 'Congratulations! You are Selected' }}</h1>
                <p>Hi {{ $name }},</p>
                <p>{{ $isUpdate ? 'Your selection details for the position have been updated by the employer. Please find the revised details below:' : 'We are thrilled to inform you that you have been selected for the position! Below are the details of your selection:' }}</p>
                
                <!-- Selection Details -->
                <div class="details-box">
                    <div class="detail-title">Offer Details</div>
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

                @if(!empty($notes))
                    <p style="font-weight: 700; margin-bottom: 8px; color: #1e293b;">Note from Employer:</p>
                    <p style="font-style: italic; background: #f8fafc; padding: 14px; border-radius: 8px; border-left: 4px solid #cbd5e1; margin: 0 0 24px 0; color: #475569;">"{{ $notes }}"</p>
                @endif

                <!-- Sourcing Partner Callout -->
                @if(!empty($partnerName))
                    <div class="partner-box">
                        <div class="partner-title">Sourcing Partner Coordinator</div>
                        <p class="partner-text">
                            This candidate was sourced by our partner <strong>{{ $partnerName }}</strong>. For further onboarding instructions, documents submission, or next steps, please contact <strong>{{ $partnerName }}</strong> directly.
                        </p>
                    </div>
                @endif

                <p>Please review your SimplyHiree account dashboard for further joining instructions and to coordinate your onboarding.</p>
                <p>We wish you the very best of luck in your new career journey!</p>
                
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
