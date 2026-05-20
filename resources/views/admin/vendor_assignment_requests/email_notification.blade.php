<!DOCTYPE html>
<html><head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; background:#eef2ff; padding:24px; margin:0; color:#0f172a;">
    <div style="max-width:640px; margin:0 auto; background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 4px 16px rgba(0,0,0,.06);">
        <div style="background:linear-gradient(135deg,#0443cd 0%,#312e81 100%); padding:24px 28px; color:#fff;">
            <div style="font-size:12px; letter-spacing:.18em; font-weight:700; color:#93c5fd; text-transform:uppercase;">SimplyHiree · Admin</div>
            <h1 style="margin:6px 0 0 0; font-size:22px; font-weight:800;">New Vendor Assignment Request</h1>
        </div>
        <div style="padding:24px 28px;">
            <p style="margin:0 0 16px 0; font-size:14px; color:#475569;">Hi <strong style="color:#0f172a;">{{ $admin->name }}</strong>,</p>
            <p style="margin:0 0 16px 0; font-size:14px; color:#0f172a;">
                <strong>{{ $client->name }}</strong> ({{ $client->email }}) has asked us to assign
                <strong style="color:#0443cd;">{{ $request->vendor_count }} vendor(s)</strong> to their account.
            </p>
            @if($request->industry_hint || $request->location_hint)
                <table style="width:100%; font-size:13px; margin:12px 0; border-collapse:collapse;">
                    @if($request->industry_hint)<tr><td style="padding:6px 0; color:#64748b; width:120px;"><strong>Industry</strong></td><td style="color:#0f172a;">{{ $request->industry_hint }}</td></tr>@endif
                    @if($request->location_hint)<tr><td style="padding:6px 0; color:#64748b;"><strong>Location</strong></td><td style="color:#0f172a;">{{ $request->location_hint }}</td></tr>@endif
                </table>
            @endif
            @if($request->notes)
                <div style="background:#f8fafc; border-left:4px solid #0443cd; padding:14px 16px; border-radius:6px; white-space:pre-wrap; font-size:13px; color:#0f172a;">
                    {{ $request->notes }}
                </div>
            @endif
            <div style="text-align:center; margin: 24px 0 8px;">
                <a href="{{ url(route('admin.vendor-assignment-requests.show', $request, false)) }}"
                   style="background:#0443cd; color:#fff; padding:12px 28px; border-radius:10px; font-weight:700; text-decoration:none; display:inline-block; font-size:14px;">
                    Open Request →
                </a>
            </div>
            <p style="margin: 24px 0 0 0; font-size: 11px; color:#94a3b8; text-align: center;">
                Reply to this email to reach {{ $client->name }} directly — Reply-To is set to their inbox.
            </p>
        </div>
    </div>
</body></html>
