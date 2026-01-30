<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .title {
            font-size: 18pt;
            font-weight: bold;
        }

        .subtitle {
            font-size: 12pt;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .meta {
            margin-bottom: 20px;
        }

        .tampered {
            background-color: #ffebee;
            color: #c62828;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">VeraPOS Security Audit Log</div>
        <div class="subtitle">Weekly Integrity Report</div>
    </div>

    <div class="meta">
        <strong>Report Generated:</strong> {{ now()->toDateTimeString() }}<br>
        <strong>Period:</strong> {{ $startDate }} to {{ $endDate }}<br>
        <strong>Total Records:</strong> {{ count($logs) }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%">Date</th>
                <th style="width: 15%">User</th>
                <th style="width: 15%">IP Address</th>
                <th style="width: 15%">Action</th>
                <th style="width: 40%">Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{{ $log->created_at }}</td>
                    <td>{{ $log->user->name ?? 'System' }} ({{ $log->user->username ?? 'N/A' }})</td>
                    <td>{{ $log->ip_address ?? 'N/A' }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>