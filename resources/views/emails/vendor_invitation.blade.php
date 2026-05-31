<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invitation to join SimplyHiree as Sourcing Partner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Inter', 'Segoe UI', Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f8fafc; padding: 40px 0; }
        .card { max-width: 600px; background: #ffffff; margin: 0 auto; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.05); }
        .header { background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%); padding: 32px 40px; text-align: center; }
        .logo { font-size: 28px; font-weight: 800; color: #ffffff; letter-spacing: -0.02em; }
        .logo span { color: #60a5fa; }
        .tagline { color: #bfdbfe; font-size: 11px; text-transform: uppercase; letter-spacing: 0.15em; font-weight: 700; margin-top: 4px; }
        .content { padding: 40px; }
        h1 { color: #1e3a8a; font-size: 22px; font-weight: 800; margin: 0 0 16px 0; letter-spacing: -0.01em; }
        p { font-size: 15px; line-height: 1.6; color: #475569; margin: 0 0 16px 0; }
        .invitation-box { background: #eff6ff; border: 1px solid #dbeafe; padding: 24px; border-radius: 12px; margin: 28px 0; }
        .invitation-title { font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #2563eb; font-weight: 800; margin-bottom: 12px; }
        .invitation-row { display: table; width: 100%; margin-bottom: 10px; font-size: 15px; }
        .invitation-row:last-child { margin-bottom: 0; }
        .invitation-label { display: table-cell; font-weight: 700; width: 120px; color: #1e3a8a; }
        .invitation-value { display: table-cell; color: #334155; }
        .btn-container { text-align: center; margin: 30px 0; }
        .btn-join { display: inline-block; background-color: #2563eb; color: #ffffff !important; font-weight: 700; font-size: 15px; text-decoration: none; padding: 12px 30px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2), 0 2px 4px -1px rgba(37, 99, 235, 0.1); transition: background-color 0.2s; }
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
                <h1>Sourcing Partner Invitation 🤝</h1>
                <p>Hi {{ $vendorName }},</p>
                <p>You have been invited by <strong>{{ $clientName }}</strong> to join SimplyHiree as their preferred Sourcing Partner!</p>
                
                <p>SimplyHiree is a state-of-the-art recruitment platform that enables agencies and employers to seamlessly collaborate on jobs, track applications, schedule interviews, and manage billing effortlessly.</p>

                <!-- Invitation Details -->
                <div class="invitation-box">
                    <div class="invitation-title">Invitation Details</div>
                    <div class="invitation-row">
                        <span class="invitation-label">Invited By:</span>
                        <span class="invitation-value">{{ $clientName }}</span>
                    </div>
                    @if(!empty($company))
                    <div class="invitation-row">
                        <span class="invitation-label">Company:</span>
                        <span class="invitation-value">{{ $company }}</span>
                    </div>
                    @endif
                </div>

                <p>To accept this invitation and set up your partner account, please click the button below to join:</p>
                
                <div class="btn-container">
                    <a href="{{ $inviteLink }}" class="btn-join">Accept Invitation & Register</a>
                </div>

                <p>If the button above does not work, copy and paste the following URL into your browser:</p>
                <p style="word-break: break-all; font-size: 13px; color: #64748b;">{{ $inviteLink }}</p>

                <p>We look forward to having you on board!</p>
                
                <p style="margin: 28px 0 0 0; color: #1e3a8a;">
                    Best regards,<br>
                    <strong>SimplyHiree Team</strong>
                </p>
            </div>

            <!-- Styled Footer -->
            <div class="footer">
                <p style="margin: 0 0 8px 0;">This is an automated invitation sent on behalf of {{ $clientName }} via SimplyHiree.</p>
                <p style="margin: 0;">
                    <a href="https://simplyhiree.com">www.simplyhiree.com</a> | 
                    <a href="https://simplyhiree.com/contact">Support</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
