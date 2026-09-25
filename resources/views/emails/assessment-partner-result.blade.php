<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f1f5f9;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px;"><tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 6px 24px rgba(15,23,42,.08);">
<tr><td style="background:linear-gradient(135deg,{{ $passed ? '#059669,#10b981' : '#e11d48,#f43f5e' }});padding:22px 28px;color:#fff;font-weight:800;font-size:18px;">SimplyHiree</td></tr>
<tr><td style="padding:30px 28px;">
<h1 style="margin:0 0 10px;font-size:19px;color:#0f172a;">Candidate assessment {{ $passed ? 'cleared' : 'result' }}</h1>
<p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#475569;">
Hi {{ $partner->name }}, your candidate <strong>{{ $candidateName }}</strong>
@if(!empty($job)) for <strong>{{ $job->title }}</strong>@endif has
@if($passed) <strong style="color:#059669;">qualified</strong> in the assessment.
@else not met the qualifying score this time.@endif
</p>
@if(!is_null($pct))
<div style="text-align:center;margin:0 0 18px;">
  <span style="display:inline-block;font-size:24px;font-weight:800;color:{{ $passed ? '#059669' : '#e11d48' }};background:{{ $passed ? '#ecfdf5' : '#fef2f2' }};border-radius:12px;padding:12px 22px;">{{ $pct }}%</span>
</div>
@endif
<p style="margin:0;font-size:13px;color:#64748b;">Log in to your SimplyHiree partner dashboard → Assessments to see the full breakdown.</p>
</td></tr>
<tr><td style="padding:16px 28px;background:#f8fafc;color:#94a3b8;font-size:11px;">© {{ date('Y') }} SimplyHiree. Automated message — please do not reply.</td></tr>
</table></td></tr></table></body></html>
