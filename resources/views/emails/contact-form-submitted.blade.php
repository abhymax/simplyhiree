<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Contact Form Submission</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #0f172a;">
    <h2 style="margin-bottom: 16px;">New Contact Form Submission</h2>

    <p><strong>Name:</strong> {{ trim($firstName . ' ' . $lastName) }}</p>
    <p><strong>Email:</strong> {{ $email }}</p>

    <div style="margin-top: 24px;">
        <p style="margin-bottom: 8px;"><strong>Message:</strong></p>
        <div style="padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; white-space: pre-wrap;">{{ $messageBody }}</div>
    </div>
</body>
</html>
