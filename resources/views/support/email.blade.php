<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Support Ticket</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f1f5f9; padding: 24px; color:#0f172a;">
    <div style="max-width: 640px; margin: 0 auto; background:#fff; border-radius: 12px; padding: 28px; box-shadow: 0 4px 16px rgba(0,0,0,.06);">
        <div style="border-bottom: 2px solid #0443cd; padding-bottom: 12px; margin-bottom: 20px;">
            <h2 style="margin: 0; color:#0443cd;">📩 New Support Ticket</h2>
            <p style="margin: 4px 0 0; color:#64748b; font-size: 13px;">Submitted via the SimplyHiree dashboard</p>
        </div>

        <table style="width:100%; font-size: 14px; margin-bottom: 16px;">
            <tr><td style="padding: 6px 0; color:#64748b; width:120px;"><strong>From:</strong></td><td>{{ $sender->name }}</td></tr>
            <tr><td style="padding: 6px 0; color:#64748b;"><strong>Email:</strong></td><td><a href="mailto:{{ $sender->email }}" style="color:#0443cd;">{{ $sender->email }}</a></td></tr>
            <tr><td style="padding: 6px 0; color:#64748b;"><strong>Role:</strong></td><td>{{ ucfirst(optional($sender->getRoleNames())->first() ?? 'user') }}</td></tr>
            <tr><td style="padding: 6px 0; color:#64748b;"><strong>User ID:</strong></td><td>#{{ $sender->id }}</td></tr>
            <tr><td style="padding: 6px 0; color:#64748b;"><strong>Subject:</strong></td><td><strong>{{ $supportSubject }}</strong></td></tr>
        </table>

        <div style="background:#f8fafc; border-left: 4px solid #0443cd; padding: 16px; border-radius: 6px;">
            <div style="white-space: pre-wrap; line-height: 1.6;">{{ $body }}</div>
        </div>

        <p style="font-size: 12px; color:#94a3b8; margin-top: 20px;">
            Reply to this email to respond directly to {{ $sender->name }}.
        </p>
    </div>
</body>
</html>
