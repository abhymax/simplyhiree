<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;color:#0f172a;background:#f1f5f9;padding:24px">
<div style="max-width:480px;margin:auto;background:#fff;border-radius:14px;overflow:hidden">
<div style="background:linear-gradient(135deg,#4f46e5,#06b6d4);padding:20px 26px;color:#fff;font-weight:800;font-size:18px">SimplyHiree</div>
<div style="padding:26px">
<h1 style="font-size:19px;margin:0 0 10px">Congratulations, {{ $letter->candidate_name }}!</h1>
<p style="font-size:14px;line-height:1.6;color:#475569">Please find your offer letter{!! $letter->job_title ? ' for the position of <b>'.e($letter->job_title).'</b>' : '' !!} attached as a PDF.</p>
<p style="font-size:13px;color:#64748b">If you have any questions, simply reply to this email.</p>
</div>
<div style="padding:14px 26px;background:#f8fafc;color:#94a3b8;font-size:11px">© {{ date('Y') }} SimplyHiree</div>
</div></body></html>
