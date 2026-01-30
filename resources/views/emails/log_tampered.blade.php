<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .alert {
            background: #fee2e2;
            border: 1px solid #ef4444;
            color: #b91c1c;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .details {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
        }

        .label {
            font-weight: bold;
            width: 150px;
            display: inline-block;
        }
    </style>
</head>

<body>
    <h2 style="color: #dc2626;">SECURITY ALERT: Tampering Detected</h2>

    <div class="alert">
        <strong>Warning:</strong> The system has detected an inconsistency in the audit log chain. This indicates
        potential unauthorized database modification.
    </div>

    <div class="details">
        <h3>Tamper Details:</h3>
        <p><span class="label">Detection Time:</span> {{ now()->toDateTimeString() }}</p>
        <p><span class="label">Status:</span> {{ $details['status'] ?? 'UNKNOWN' }}</p>
        <p><span class="label">Log ID:</span> {{ $details['log_id'] ?? 'N/A' }}</p>
        <p><span class="label">Reason:</span> {{ $details['reason'] ?? 'N/A' }}</p>

        @if(isset($details['user']))
            <p><span class="label">Associated User:</span> {{ $details['user'] }}</p>
        @endif

        @if(isset($details['date']))
            <p><span class="label">Date:</span> {{ $details['date'] }}</p>
        @endif
    </div>

    <p>Please investigate this issue immediately.</p>
    <p><em>VeraPOS Security System</em></p>
</body>

</html>