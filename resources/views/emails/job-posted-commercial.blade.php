<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0;padding:0;background:#eef3fb;color:#17213a;font-family:Arial,Helvetica,sans-serif;">
    @php
        $manualCommercial = $job->commercial_source === 'manual';
        $commercialValue = $manualCommercial
            ? ($job->fee_type === 'percentage'
                ? rtrim(rtrim(number_format((float) $job->fee_amount, 2), '0'), '.').'%'
                : '₹'.number_format((float) $job->fee_amount, 2).' flat')
            : 'SimplyHiree client agreement';
        $maturityDays = $job->client_payout_days ?? $job->minimum_stay_days;
        $replacementDays = $job->replacement_period_days ?? $job->replacement_guarantee_days;
    @endphp

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#eef3fb;">
        <tr>
            <td align="center" style="padding:28px 12px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:640px;background:#ffffff;border:1px solid #dce6f5;border-radius:18px;overflow:hidden;box-shadow:0 16px 40px rgba(30,64,175,.12);">
                    <tr>
                        <td style="padding:28px 32px;background:#0b1d46;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td valign="middle">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="width:46px;height:46px;text-align:center;vertical-align:middle;background:#2f6fed;border-radius:12px;color:#ffffff;font-size:18px;font-weight:800;">SH</td>
                                                <td style="padding-left:12px;color:#ffffff;font-size:23px;font-weight:800;">Simply<span style="color:#5aa2ff;">Hiree</span></td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td align="right" valign="middle">
                                        <span style="display:inline-block;padding:7px 11px;border-radius:999px;background:#fff2bf;color:#8a5a00;font-size:11px;font-weight:800;text-transform:uppercase;">Pending approval</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:34px 32px 18px;">
                            <div style="font-size:13px;font-weight:800;letter-spacing:.6px;text-transform:uppercase;color:#2f6fed;">
                                {{ $isAdmin ? 'New client requirement' : 'Posting confirmed' }}
                            </div>
                            <h1 style="margin:9px 0 12px;font-size:28px;line-height:1.22;color:#111c36;">
                                {{ $isAdmin ? 'A new job is ready for review' : 'Your job was posted successfully' }}
                            </h1>
                            <p style="margin:0;color:#5f6b82;font-size:15px;line-height:1.65;">
                                @if($isAdmin)
                                    {{ $client->name }} submitted a new requirement. Review the job and confirm the final partner payout before making it live.
                                @else
                                    Hi {{ $client->name }}, your requirement has reached the SimplyHiree team. We will review the details and notify you when it is approved.
                                @endif
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:8px 32px 24px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f6f9ff;border:1px solid #dce7fa;border-radius:14px;">
                                <tr>
                                    <td colspan="2" style="padding:20px 22px 14px;border-bottom:1px solid #dce7fa;">
                                        <div style="font-size:20px;line-height:1.35;font-weight:800;color:#111c36;">{{ $job->title }}</div>
                                        <div style="margin-top:5px;font-size:13px;color:#64748b;">{{ $job->company_name }} &middot; {{ $job->location }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="50%" style="padding:16px 22px;border-right:1px solid #dce7fa;border-bottom:1px solid #dce7fa;">
                                        <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#7c8aa5;">Hiring flow</div>
                                        <div style="margin-top:5px;font-size:14px;font-weight:700;color:#17213a;">{{ $job->screening_required ? 'SimplyHiree screening' : 'Direct interview' }}</div>
                                    </td>
                                    <td width="50%" style="padding:16px 22px;border-bottom:1px solid #dce7fa;">
                                        <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#7c8aa5;">Openings</div>
                                        <div style="margin-top:5px;font-size:14px;font-weight:700;color:#17213a;">{{ $job->openings ?? 1 }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="50%" style="padding:16px 22px;border-right:1px solid #dce7fa;">
                                        <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#7c8aa5;">Commercial</div>
                                        <div style="margin-top:5px;font-size:14px;font-weight:700;color:#17213a;">{{ $commercialValue }}</div>
                                    </td>
                                    <td width="50%" style="padding:16px 22px;">
                                        <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#7c8aa5;">Commercial terms</div>
                                        <div style="margin-top:5px;font-size:14px;font-weight:700;color:#17213a;">
                                            @if($manualCommercial)
                                                {{ $maturityDays ?? '—' }}-day maturity &middot; {{ $replacementDays ?? '—' }}-day replacement
                                            @else
                                                Account agreement applies
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:0 32px 34px;">
                            <a href="{{ $actionUrl }}" style="display:inline-block;padding:14px 26px;background:#2f6fed;color:#ffffff;text-decoration:none;border-radius:10px;font-size:14px;font-weight:800;">{{ $actionLabel }} &rarr;</a>
                            <p style="margin:18px 0 0;color:#8a96aa;font-size:11px;line-height:1.5;">Job reference #{{ $job->id }} &middot; Posted {{ $job->created_at?->format('d M Y, h:i A') }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 32px;background:#0b1d46;color:#9fb0d2;font-size:11px;line-height:1.6;text-align:center;">
                            Accelerating executive recruitment<br>
                            <span style="color:#ffffff;font-weight:700;">Simply Hiree Private Limited</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
