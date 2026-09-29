<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f1f5f9;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px;"><tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 6px 24px rgba(15,23,42,.08);">
<tr><td style="background:linear-gradient(135deg,#4f46e5,#06b6d4);padding:22px 28px;color:#fff;font-weight:800;font-size:18px;">SimplyHiree</td></tr>
<tr><td style="padding:30px 28px;">
<h1 style="margin:0 0 10px;font-size:20px;color:#0f172a;">Stage {{ $stageOrder }} unlocked</h1>
<p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#475569;">Great work {{ $session->candidate_name ?: 'there' }}! You cleared the previous stage{!! !empty($job) ? ' for <strong>'.e($job->title).'</strong>' : '' !!}. Stage {{ $stageOrder }} is now ready for you.</p>
<div style="text-align:center;margin:0 0 20px;"><a href="{{ $url }}" style="display:inline-block;background:linear-gradient(135deg,#4f46e5,#06b6d4);color:#fff;text-decoration:none;font-weight:800;font-size:15px;padding:14px 28px;border-radius:12px;">Continue assessment</a></div>
<p style="margin:0;font-size:12px;color:#64748b;">If there's a time limit to begin the next stage, please continue soon so you don't miss the window.</p>
</td></tr>
<tr><td style="padding:16px 28px;background:#f8fafc;color:#94a3b8;font-size:11px;">© {{ date('Y') }} SimplyHiree. Automated message — please do not reply.</td></tr>
</table></td></tr></table></body></html>
