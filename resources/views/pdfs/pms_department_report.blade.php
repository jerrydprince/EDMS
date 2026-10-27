<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Departmental PMS Report - {{ $department->title ?? ($department->name ?? 'Department') }}</title>
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
            font-size: 10.5px;
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
                <div class="header-title">Departmental Performance Summary Report</div>
                <div class="header-subtitle">{{ $department->title ?? ($department->name ?? 'Department') }} • Executive Oversight</div>
            </td>
            <td style="text-align: right; vertical-align: middle;">
                <div class="report-badge">EXECUTIVE SUMMARY</div>
                <div style="font-size: 9.5px; color: #64748b; margin-top: 4px;">Date: {{ date('d M Y, h:i A') }}</div>
            </td>
        </tr>
    </table>

    <table class="metric-box-table">
        <tr>
            <td style="width: 25%;">
                <div class="metric-box">
                    <div class="metric-lbl">Total Personnel</div>
                    <div class="metric-val">{{ count($users) }}</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="metric-box">
                    <div class="metric-lbl">Active on Duty</div>
                    <div class="metric-val" style="color: #15803d;">{{ $activeCount }}</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="metric-box">
                    <div class="metric-lbl">On Leave</div>
                    <div class="metric-val" style="color: #b45309;">{{ $onLeaveCount }}</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="metric-box">
                    <div class="metric-lbl">Configured KRAs</div>
                    <div class="metric-val" style="color: #1d4ed8;">{{ count($kras) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Department Personnel Roster & Status</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 30%;">Staff Member</th>
                <th style="width: 25%;">Cadre / Role</th>
                <th style="width: 25%;">Unit / Division</th>
                <th style="width: 15%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $index => $u)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $u->first_name }} {{ $u->last_name }}</strong>
                        <div style="font-size: 9.5px; color: #64748b;">{{ $u->email }}</div>
                    </td>
                    <td>{{ $u->roles[0]->name ?? 'Staff' }}</td>
                    <td>{{ $u->unit->title ?? ($u->unit->name ?? ($u->division->title ?? ($u->division->name ?? 'General'))) }}</td>
                    <td>
                        @if($u->is_on_leave == 1)
                            <span style="color: #b45309; font-weight: bold;">On Leave</span>
                        @else
                            <span style="color: #15803d; font-weight: bold;">Active</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #94a3b8; padding: 12px;">No personnel records found for this department.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Established Key Result Areas (KRAs)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35%;">Key Result Area</th>
                <th style="width: 45%;">Description</th>
                <th style="width: 20%;">Total Indicators</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kras as $kra)
                <tr>
                    <td style="font-weight: bold;">{{ $kra->name }}</td>
                    <td>{{ $kra->description ?? 'Standard Departmental Deliverables' }}</td>
                    <td style="font-weight: bold;">{{ count($kra->kpi) }} Indicators</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #94a3b8; padding: 10px;">No KRAs configured yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated automatically by the Executive Electronic Document Management & Performance Management System (EDMS).
    </div>
</body>
</html>
