<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f1f5f9;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px;"><tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 6px 24px rgba(15,23,42,.08);">
<tr><td style="background:linear-gradient(135deg,{{ $passed ? '#059669,#10b981' : '#4f46e5,#06b6d4' }});padding:22px 28px;color:#fff;font-weight:800;font-size:18px;">SimplyHiree</td></tr>
<tr><td style="padding:30px 28px;">
@if($passed)
<h1 style="margin:0 0 10px;font-size:20px;color:#0f172a;">You've qualified! 🎉</h1>
<p style="margin:0 0 18px;font-size:14px;line-height:1.6;color:#475569;">Congratulations {{ $session->candidate_name ?: 'there' }} — you cleared every stage of the assessment@if(!empty($job)) for <strong>{{ $job->title }}</strong>@endif. Your results have been shared with the recruiter. No further action is needed from you right now.</p>
@else
<h1 style="margin:0 0 10px;font-size:20px;color:#0f172a;">Assessment complete</h1>
<p style="margin:0 0 18px;font-size:14px;line-height:1.6;color:#475569;">Thank you for completing the assessment{{ !empty($job) ? ' for '.$job->title : '' }}, {{ $session->candidate_name ?: 'there' }}. Unfortunately you didn't meet the pass mark this time. Please reach out to the recruiter who invited you for any next steps.</p>
@endif
</td></tr>
<tr><td style="padding:16px 28px;background:#f8fafc;color:#94a3b8;font-size:11px;">© {{ date('Y') }} SimplyHiree. Automated message — please do not reply.</td></tr>
</table></td></tr></table></body></html>
