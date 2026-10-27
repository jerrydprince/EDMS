<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Staff Attendance Log Report - {{ $user->first_name }} {{ $user->last_name }}</title>
    <style>
        @page {
            margin: 28px 32px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 0;
            line-height: 1.45;
        }
        .header-table {
            width: 100%;
            border-bottom: 2.5px solid #1e3a8a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
        }
        .header-subtitle {
            font-size: 11px;
            color: #64748b;
            margin: 0;
        }
        .report-badge {
            display: inline-block;
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-top: 14px;
            margin-bottom: 8px;
        }
        .info-table, .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .info-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            font-size: 10.5px;
        }
        .info-label {
            background-color: #f8fafc;
            font-weight: bold;
            color: #475569;
            width: 25%;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            font-size: 10px;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
        }
        .metric-box-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 12px;
        }
        .metric-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px;
            text-align: center;
        }
        .metric-val {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }
        .metric-lbl {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            color: #64748b;
        }
        .footer {
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="header-title">Staff Attendance & Login Session Report</div>
                <div class="header-subtitle">{{ $user->first_name }} {{ $user->last_name }} ({{ $user->roles[0]->name ?? 'Officer' }}) • Official Log</div>
            </td>
            <td style="text-align: right; vertical-align: middle;">
                <div class="report-badge">AUDIT VERIFIED</div>
                <div style="font-size: 9.5px; color: #64748b; margin-top: 4px;">Date: {{ date('d M Y, h:i A') }}</div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="info-label">Staff Name:</td>
            <td style="font-weight: bold;">{{ $user->first_name }} {{ $user->last_name }}</td>
            <td class="info-label">Cadre / Department:</td>
            <td>{{ $user->department->title ?? ($user->department->name ?? 'General') }}</td>
        </tr>
        <tr>
            <td class="info-label">Reporting Period:</td>
            <td colspan="3">
                @if($startDate || $endDate)
                    From <strong>{{ $startDate ? date('d M Y', strtotime($startDate)) : 'Beginning' }}</strong> to <strong>{{ $endDate ? date('d M Y', strtotime($endDate)) : 'Present' }}</strong>
                @else
                    All Recorded Sessions to Date
                @endif
            </td>
        </tr>
    </table>

    <table class="metric-box-table">
        <tr>
            <td style="width: 25%;">
                <div class="metric-box">
                    <div class="metric-lbl">Total Days Logged</div>
                    <div class="metric-val">{{ count($attendanceLogs) }}</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="metric-box">
                    <div class="metric-lbl">Completed Sessions</div>
                    <div class="metric-val" style="color: #15803d;">{{ $completedCount }}</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="metric-box">
                    <div class="metric-lbl">Avg Daily Duration</div>
                    <div class="metric-val" style="color: #1d4ed8;">{{ $avgDailyDuration }}</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="metric-box">
                    <div class="metric-lbl">Unclosed Sessions</div>
                    <div class="metric-val" style="color: #b45309;">{{ count($attendanceLogs) - $completedCount }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Attendance & Time-Tracking Records</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;">#</th>
                <th style="width: 28%;">Date</th>
                <th style="width: 22%;">Check In</th>
                <th style="width: 22%;">Check Out</th>
                <th style="width: 20%;">Total Duration</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendanceLogs as $index => $log)
                @php
                    $duration = '--';
                    if ($log->login_time && $log->logout_time) {
                        $start = \Carbon\Carbon::parse($log->login_time);
                        $end = \Carbon\Carbon::parse($log->logout_time);
                        $hours = $end->diffInHours($start);
                        $mins = $end->diffInMinutes($start) % 60;
                        $duration = "{$hours}h {$mins}m";
                    }
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="font-weight: bold;">{{ date('D, d M Y', strtotime($log->created_at ?? $log->login_time)) }}</td>
                    <td>{{ $log->login_time ? date('h:i A', strtotime($log->login_time)) : 'N/A' }}</td>
                    <td>{{ $log->logout_time ? date('h:i A', strtotime($log->logout_time)) : 'Active / NIL' }}</td>
                    <td style="font-weight: bold; color: #1e3a8a;">{{ $duration }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #94a3b8; padding: 12px;">No attendance sessions found for the specified period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated automatically by the Executive Electronic Document Management & Performance Management System (EDMS).
    </div>
</body>
</html>
