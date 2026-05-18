<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Approved Candidates — Daily Digest</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; background:#eef2ff; padding: 24px; color:#0f172a; margin: 0;">
    <div style="max-width: 760px; margin: 0 auto;">
        {{-- Hero --}}
        <div style="background: linear-gradient(135deg, #0443cd 0%, #312e81 100%); border-radius: 14px 14px 0 0; padding: 28px 32px; color: #fff;">
            <div style="font-size: 13px; letter-spacing: 0.18em; font-weight: 700; color: #93c5fd; text-transform: uppercase; margin-bottom: 6px;">SimplyHiree · Daily Digest</div>
            <h1 style="margin: 0 0 6px 0; font-size: 26px; font-weight: 800;">Approved Candidates for Your Jobs</h1>
            <p style="margin: 0; color: #cbd5e1; font-size: 14px;">{{ $applications->count() }} candidate{{ $applications->count() === 1 ? '' : 's' }} approved by SimplyHiree on {{ $date->format('l, d M Y') }}</p>
        </div>

        {{-- Body --}}
        <div style="background: #fff; border-radius: 0 0 14px 14px; padding: 24px 32px; box-shadow: 0 4px 16px rgba(0,0,0,.06);">
            <p style="margin: 0 0 16px 0; font-size: 15px; color: #475569;">
                Hi <strong style="color:#0f172a;">{{ $client->name }}</strong>,
            </p>
            <p style="margin: 0 0 22px 0; font-size: 14px; line-height: 1.6; color: #475569;">
                Here are the candidates that our team approved for your open positions. Log in to your dashboard to schedule interviews or mark selections.
            </p>

            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: #f1f5f9; text-align: left;">
                        <th style="padding: 10px 8px; color: #0443cd; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Candidate</th>
                        <th style="padding: 10px 8px; color: #0443cd; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Job</th>
                        <th style="padding: 10px 8px; color: #0443cd; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Experience</th>
                        <th style="padding: 10px 8px; color: #0443cd; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Current</th>
                        <th style="padding: 10px 8px; color: #0443cd; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Source</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $app)
                        @php
                            $cand   = $app->candidate;
                            $prof   = $app->candidateUser?->profile;
                            $job    = $app->job;
                            $name   = $cand
                                ? trim(($cand->first_name ?? '').' '.($cand->last_name ?? ''))
                                : ($app->candidateUser?->name ?? 'N/A');
                            $email  = $cand?->email ?? $app->candidateUser?->email ?? '';
                            $phone  = $cand?->phone ?? $prof?->phone_number ?? '';
                            $expY   = $cand?->total_experience_years ?? $prof?->total_experience_years;
                            $expM   = $cand?->total_experience_months ?? $prof?->total_experience_months;
                            $totalExp = ($expY === null && $expM === null) ? ($cand?->experience_status ?? '—') : ((int) ($expY ?? 0)).'y '.((int) ($expM ?? 0)).'m';
                            $curCo  = $cand?->current_company_name ?? $prof?->current_company_name ?? '—';
                            $curDes = $cand?->current_designation ?? $prof?->current_designation ?? '';
                            $curSal = $cand?->current_salary ?? $prof?->current_salary ?? '';
                            $loc    = $cand?->current_location ?? $prof?->current_location ?? '';
                            $partnerName = $cand?->partner?->name ?? 'Direct';
                            $appCode = $app->application_code ?? ('SH-APP-'.str_pad((string)$app->id, 6, '0', STR_PAD_LEFT));
                        @endphp
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 12px 8px; vertical-align: top;">
                                <div style="font-weight: 700; color: #0f172a;">{{ $name }}</div>
                                <div style="color: #475569; font-size: 12px;">{{ $email }}</div>
                                @if($phone)<div style="color: #475569; font-size: 12px;">{{ $phone }}</div>@endif
                                <div style="color: #94a3b8; font-size: 11px; margin-top: 2px;">{{ $appCode }}</div>
                            </td>
                            <td style="padding: 12px 8px; vertical-align: top;">
                                <div style="font-weight: 700; color: #0f172a;">{{ $job?->title ?? '—' }}</div>
                                @if($loc)<div style="color: #475569; font-size: 12px;">📍 {{ $loc }}</div>@endif
                            </td>
                            <td style="padding: 12px 8px; vertical-align: top; color: #475569;">{{ $totalExp }}</td>
                            <td style="padding: 12px 8px; vertical-align: top;">
                                <div style="color: #475569; font-size: 12px;">{{ $curDes }}</div>
                                <div style="color: #94a3b8; font-size: 11px;">{{ $curCo }}</div>
                                @if($curSal)<div style="color: #0443cd; font-size: 11px; font-weight: 700;">₹{{ number_format((float) preg_replace('/[^0-9.]/', '', (string) $curSal)) }}</div>@endif
                            </td>
                            <td style="padding: 12px 8px; vertical-align: top;">
                                <span style="background: #ede9fe; color: #6d28d9; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px;">{{ $partnerName }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 24px; padding: 14px 18px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 8px;">
                <div style="font-weight: 700; color: #92400e; font-size: 13px; margin-bottom: 4px;">
                    📎 Full candidate tracker attached as CSV
                </div>
                <p style="margin: 0; color: #78350f; font-size: 12px; line-height: 1.5;">
                    The attached spreadsheet contains the complete details for all {{ $applications->count() }} candidate{{ $applications->count() === 1 ? '' : 's' }} — including phone, preferred locations, qualification, expected salary, notice period and more.
                </p>
            </div>

            <div style="margin-top: 24px; text-align: center;">
                <a href="{{ url(route('client.applications.index', [], false)) }}"
                   style="background: #0443cd; color: #fff; padding: 12px 28px; border-radius: 10px; font-weight: 700; text-decoration: none; display: inline-block; font-size: 14px;">
                    Open Dashboard →
                </a>
            </div>

            <p style="margin: 28px 0 0 0; font-size: 12px; color: #94a3b8; text-align: center;">
                You're receiving this daily digest because you have active jobs on SimplyHiree. Questions? Reply to this email.
            </p>
        </div>

        <p style="margin: 16px 0 0 0; font-size: 11px; color: #94a3b8; text-align: center;">
            &copy; {{ now()->format('Y') }} SimplyHiree. All rights reserved.
        </p>
    </div>
</body>
</html>
