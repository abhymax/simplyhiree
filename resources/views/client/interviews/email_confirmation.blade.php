<!DOCTYPE html>
<html><head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; background:#eef2ff; padding:24px; margin:0; color:#0f172a;">
    <div style="max-width:640px; margin:0 auto; background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 4px 16px rgba(0,0,0,.06);">
        <div style="background:linear-gradient(135deg,#0443cd 0%,#312e81 100%); padding:24px 28px; color:#fff;">
            <div style="font-size:12px; letter-spacing:.18em; font-weight:700; color:#93c5fd; text-transform:uppercase;">SimplyHiree · Interview</div>
            <h1 style="margin:6px 0 0 0; font-size:22px; font-weight:800;">{{ $isUpdate ? 'Your Interview Time Was Updated' : 'Your Interview Is Scheduled' }}</h1>
        </div>

        <div style="padding:24px 28px;">
            <p style="margin:0 0 18px 0; font-size:14px; color:#475569;">Hi <strong style="color:#0f172a;">{{ $name }}</strong>,</p>

            <p style="margin:0 0 16px 0; font-size:14px; line-height:1.6; color:#0f172a;">
                {{ $isUpdate ? 'Your interview time has been updated. Please find the latest details below:' : 'Your interview has been scheduled. Please find the details below:' }}
            </p>

            <table style="width:100%; border-collapse:collapse; margin: 12px 0 18px; font-size:14px;">
                <tr><td style="padding:8px 0; color:#64748b; width:130px;"><strong>🏢 Company</strong></td><td style="color:#0f172a;">{{ $company }}</td></tr>
                <tr><td style="padding:8px 0; color:#64748b;"><strong>💼 Role</strong></td><td style="color:#0f172a;">{{ $role }}</td></tr>
                <tr><td style="padding:8px 0; color:#64748b;"><strong>🕒 When</strong></td><td style="color:#0f172a; font-weight:700;">{{ $time }}</td></tr>
                @if($meeting_link)
                    <tr><td style="padding:8px 0; color:#64748b;"><strong>🔗 Join</strong></td><td><a href="{{ $meeting_link }}" style="color:#0443cd; word-break:break-all;">{{ $meeting_link }}</a></td></tr>
                @elseif($location)
                    <tr><td style="padding:8px 0; color:#64748b;"><strong>📍 Where</strong></td><td style="color:#0f172a;">{{ $location }}</td></tr>
                @endif
                @if($notes)
                    <tr><td style="padding:8px 0; color:#64748b; vertical-align:top;"><strong>📝 Note</strong></td><td style="color:#0f172a;">{{ $notes }}</td></tr>
                @endif
            </table>

            @if($meeting_link)
                <div style="text-align:center; margin: 18px 0;">
                    <a href="{{ $meeting_link }}" style="background:#0443cd; color:#fff; padding:12px 28px; border-radius:10px; font-weight:700; text-decoration:none; display:inline-block; font-size:14px;">
                        Join the Interview →
                    </a>
                </div>
            @endif

            <p style="margin:24px 0 0 0; font-size:13px; color:#475569; line-height:1.6;">
                Tips:
                <br>• Be online 5 minutes early
                <br>• Keep a printed/digital copy of your resume handy
                <br>• Ensure stable internet and a quiet space
            </p>

            <p style="margin:24px 0 0 0; font-size:12px; color:#94a3b8; text-align:center;">
                Best of luck! — Team SimplyHiree
            </p>
        </div>
    </div>
</body></html>
