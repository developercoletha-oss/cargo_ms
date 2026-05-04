<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Approved</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 24px;">
    <div style="max-width: 620px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
        <div style="background: #1d4ed8; color: #ffffff; padding: 20px 24px;">
            <h2 style="margin: 0; font-size: 20px;">CFTMS Account Approved</h2>
        </div>

        <div style="padding: 24px;">
            <p style="margin-top: 0;">Hello {{ $user->full_name ?: $user->name }},</p>
            <p>Your customer account has been approved by the administrator. You can now log in and start using the system.</p>

            <p style="margin: 18px 0;">
                <a href="{{ route('login') }}" style="display: inline-block; background: #2563eb; color: #ffffff; text-decoration: none; padding: 10px 16px; border-radius: 8px;">
                    Login to CFTMS
                </a>
            </p>

            <p style="margin-bottom: 0;">Thank you,<br>CFTMS Team</p>
        </div>
    </div>
</body>
</html>

