<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; background-color: #f3f4f6; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn { display: inline-block; background-color: #ef4444; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; margin-top: 20px; }
        .details { background-color: #f9fafb; padding: 15px; border-radius: 4px; margin: 20px 0; border: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login Attempt Blocked</h2>
        <p>We noticed a login attempt that was blocked because another device is currently active.</p>
        
        <div class="details">
            <p><strong>Device:</strong> {{ $deviceDetails['device'] }}</p>
            <p><strong>IP Address:</strong> {{ $deviceDetails['ip'] }}</p>
            <p><strong>Time:</strong> {{ now()->toDateTimeString() }}</p>
        </div>

        <p>If this was you and you cannot access your other device, click the button below to force a login. <strong>This will log out the other device immediately.</strong></p>

        <a href="{{ $this->url }}" class="btn">Force Login to This Device</a>

        <p style="margin-top: 30px; font-size: 12px; color: #6b7280;">If this wasn't you, please ignore this email. Your account remains secure.</p>
    </div>
</body>
</html>