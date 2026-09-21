<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 520px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #4F46E5, #7C3AED); padding: 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; }
        .body { padding: 30px; }
        .body p { color: #374151; line-height: 1.6; font-size: 15px; }
        .btn { display: block; width: fit-content; margin: 24px auto; background: #4F46E5; color: #ffffff !important; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 16px; }
        .note { background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 12px 16px; border-radius: 4px; font-size: 13px; color: #92400E; margin-top: 20px; }
        .footer { background: #F9FAFB; padding: 16px; text-align: center; font-size: 12px; color: #9CA3AF; border-top: 1px solid #E5E7EB; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Subeditor24</h1>
        </div>
        <div class="body">
            <p>Hello <strong>{{ $user->name ?? 'User' }}</strong>,</p>
            <p>You recently requested to reset your password. Click the button below to set a new password:</p>

            <a href="{{ $resetLink }}" class="btn">🔑 Reset Password</a>

            <div class="note">
                ⏰ This link will expire in <strong>60 minutes</strong>.<br>
                If you did not request this password reset, please ignore this email.
            </div>

            <p style="margin-top:20px; font-size:13px; color:#6B7280; word-break:break-all;">
                If the button above does not work, copy and paste the following URL into your browser:<br>
                <a href="{{ $resetLink }}" style="color:#4F46E5;">{{ $resetLink }}</a>
            </p>
        </div>
        <div class="footer">
            © {{ date('Y') }} Subeditor24 | info@subeditor24.net
        </div>
    </div>
</body>
</html>
