<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verification Code</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 40px 20px;">
    <div style="max-width: 400px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; padding: 40px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="margin-bottom: 24px;">
            <svg width="150" height="40" viewBox="0 0 150 40" xmlns="http://www.w3.org/2000/svg">
                <rect x="0" y="0" width="40" height="40" rx="8" fill="#0F766E"/>
                <text x="20" y="26" font-family="Arial, sans-serif" font-size="15" font-weight="900" fill="white" text-anchor="middle">HD</text>
                <text x="55" y="27" font-family="Arial, sans-serif" font-size="20" font-weight="700" fill="#333">HelpDesk</text>
            </svg>
        </div>
        <h1 style="color: #333; font-size: 24px; margin-bottom: 10px;">Verify your email</h1>
        <p style="color: #666; font-size: 14px; margin-bottom: 30px;">
            Enter this code to verify your email address
        </p>
        
        <div style="background-color: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
            <span style="font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #333;">{{ $code }}</span>
        </div>
        
        <p style="color: #999; font-size: 12px; margin-bottom: 0;">
            This code expires in 1 minute.
        </p>
        <p style="color: #999; font-size: 12px;">
            If you didn't request this code, you can safely ignore this email.
        </p>
    </div>
</body>
</html>
