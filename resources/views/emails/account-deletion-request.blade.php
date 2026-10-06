<h2>SimplyHiree account deletion request</h2>

<p><strong>Reference:</strong> {{ $deletionRequest->reference }}</p>
<p><strong>Name:</strong> {{ $deletionRequest->name }}</p>
<p><strong>Email:</strong> {{ $deletionRequest->email }}</p>
<p><strong>Account type:</strong> {{ str_replace('_', ' ', ucfirst($deletionRequest->account_type)) }}</p>
<p><strong>Account matched:</strong> {{ $accountFound ? 'Yes' : 'No - verify the email with the requester' }}</p>
<p><strong>Reason:</strong> {{ $deletionRequest->reason ?: 'Not provided' }}</p>
<p><strong>Requested:</strong> {{ $deletionRequest->requested_at->format('d M Y, h:i A') }}</p>

<p>Verify account ownership before deleting or anonymising any data.</p>
