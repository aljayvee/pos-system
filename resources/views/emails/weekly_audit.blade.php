<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
        }

        .info-box {
            background: #e0f2fe;
            border-left: 4px solid #0ea5e9;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2 style="color: #0f172a;">Weekly Audit Log Report</h2>
            <p style="color: #64748b;">Generated on {{ now()->toFormattedDateString() }}</p>
        </div>

        <p>Hello,</p>
        <p>Attached is the weekly security audit log report for your review.</p>

        <div class="info-box">
            <strong>🔒 Protected Document</strong><br>
            This file is encrypted for your security.<br><br>
            <strong>Password Format:</strong><br>
            <code>Birthdate(YYYYMMDD) + Username + FirstName</code>
        </div>

        <p>If you did not request this report or start noticing suspicious entries, please contact the system
            administrator immediately.</p>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">
        <p style="font-size: 0.8rem; color: #999;">
            This is an automated message from VeraPOS Security System.<br>
            Do not reply to this email.
        </p>
    </div>
</body>

</html>