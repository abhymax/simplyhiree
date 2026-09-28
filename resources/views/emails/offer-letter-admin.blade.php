<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;color:#0f172a">
<h2>Offer letter sent</h2>
<p><b>{{ $letter->candidate_name }}</b> ({{ $letter->candidate_email }}) — {{ $letter->job_title }} @ {{ $letter->company_name }}</p>
<p>Sent {{ now()->format('d M Y, h:i A') }}. PDF attached.</p>
</body></html>
