<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PMS Scorecard - {{ $user->first_name }} {{ $user->last_name }}</title>
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
            width: 22%;
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
        .signoff-table {
            width: 100%;
            margin-top: 28px;
            border-collapse: collapse;
        }
        .signoff-table td {
            width: 33.3%;
            padding: 0 10px;
            vertical-align: top;
        }
        .signoff-line {
            border-top: 1px solid #334155;
            margin-top: 36px;
            padding-top: 4px;
            font-size: 10px;
            text-align: center;
            font-weight: bold;
            color: #334155;
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
    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="header-title">Staff Performance Evaluation & Scorecard</div>
                <div class="header-subtitle">Performance Management System (PMS) Official Record</div>
            </td>
            <td style="text-align: right; vertical-align: middle;">
                <div class="report-badge">CONFIDENTIAL</div>
                <div style="font-size: 10px; color: #64748b; margin-top: 4px;">Ref: PMS-{{ str_pad($user->id, 6, '0', STR_PAD_LEFT) }}-{{ date('Y') }}</div>
                <div style="font-size: 9.5px; color: #64748b;">Generated: {{ date('d M Y, h:i A') }}</div>
            </td>
        </tr>
    </table>

    <!-- Personnel Details -->
    <div class="section-title">1. Personnel Information</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Full Name:</td>
            <td style="font-weight: bold;">{{ $user->first_name }} {{ $user->last_name }}</td>
            <td class="info-label">Cadre / Position:</td>
            <td>{{ $user->roles[0]->name ?? 'Officer' }}</td>
        </tr>
        <tr>
            <td class="info-label">Department:</td>
            <td>{{ $user->department->title ?? ($user->department->name ?? 'N/A') }}</td>
            <td class="info-label">Division / Unit:</td>
            <td>{{ $user->unit->title ?? ($user->unit->name ?? ($user->division->title ?? ($user->division->name ?? 'Headquarters'))) }}</td>
        </tr>
        <tr>
            <td class="info-label">Email Address:</td>
            <td>{{ $user->email }}</td>
            <td class="info-label">Phone Number:</td>
            <td>{{ $user->phone ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="info-label">Duty Status:</td>
            <td colspan="3">
                @if($user->is_on_leave == 1)
                    <span style="color: #b45309; font-weight: bold;">ON LEAVE</span>
                @else
                    <span style="color: #15803d; font-weight: bold;">ACTIVE ON DUTY</span>
                @endif
            </td>
        </tr>
    </table>

    <!-- Task Execution & Turnaround KPI Metrics -->
    <div class="section-title">2. Task Execution & Turnaround Performance</div>
    <table class="metric-box-table">
        <tr>
            <td style="width: 20%;">
                <div class="metric-box">
                    <div class="metric-lbl">Total Tasks</div>
                    <div class="metric-val">{{ $taskMetrics['total_tasks'] }}</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="metric-box">
                    <div class="metric-lbl">Completed</div>
                    <div class="metric-val" style="color: #15803d;">{{ $taskMetrics['completion_rate'] }}%</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="metric-box">
                    <div class="metric-lbl">Pending</div>
                    <div class="metric-val" style="color: #b45309;">{{ $taskMetrics['pending_rate'] }}%</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="metric-box">
                    <div class="metric-lbl">Avg Turnaround</div>
                    <div class="metric-val" style="font-size: 13px;">{{ $taskMetrics['avg_completion_time'] }}</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="metric-box">
                    <div class="metric-lbl">On-Time Rate</div>
                    <div class="metric-val" style="color: #1d4ed8;">{{ $taskMetrics['on_time_rate'] }}%</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Department KRAs & Measurable KPIs -->
    <div class="section-title">3. Department Key Result Areas (KRAs) & Evaluation Metrics</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30%;">Key Result Area</th>
                <th style="width: 35%;">Key Performance Indicator (KPI)</th>
                <th style="width: 20%;">Metric Model</th>
                <th style="width: 15%;">Benchmark</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kras as $kra)
                @forelse($kra->kpi as $kpiIndex => $kpi)
                    <tr>
                        @if($kpiIndex === 0)
                            <td rowspan="{{ count($kra->kpi) }}" style="vertical-align: top; font-weight: bold; background-color: #f8fafc;">
                                {{ $kra->name }}
                                @if($kra->description)
                                    <div style="font-size: 9px; color: #64748b; font-weight: normal; margin-top: 2px;">{{ $kra->description }}</div>
                                @endif
                            </td>
                        @endif
                        <td>
                            <strong style="text-transform: capitalize;">{{ $kpi->key }}</strong>
                            @if($kpi->description)
                                <div style="font-size: 9px; color: #64748b; margin-top: 2px;">{{ $kpi->description }}</div>
                            @endif
                        </td>
                        <td>
                            @if($kpi->string == 'point')
                                5-Point Rating Scale
                            @elseif($kpi->string == 'percentage')
                                Percentage Target
                            @else
                                Turnaround Time
                            @endif
                        </td>
                        <td style="font-weight: bold;">
                            @if($kpi->string == 'point')
                                {{ $kpi->value }} Points
                            @elseif($kpi->string == 'percentage')
                                100%
                            @else
                                Timely
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td style="font-weight: bold;">{{ $kra->name }}</td>
                        <td colspan="3" style="color: #94a3b8; font-style: italic;">No specific KPIs configured for this KRA.</td>
                    </tr>
                @endforelse
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #64748b; padding: 12px;">No departmental KRAs configured for this staff member's department yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Attendance & Login Summary -->
    <div class="section-title">4. Attendance & Punctuality Profile</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Total Logged Days:</td>
            <td style="font-weight: bold;">{{ $attendanceSummary['total_days'] }} Days</td>
            <td class="info-label">Completed Sessions:</td>
            <td style="font-weight: bold;">{{ $attendanceSummary['completed_sessions'] }}</td>
        </tr>
        <tr>
            <td class="info-label">Average Daily Hours:</td>
            <td style="font-weight: bold;">{{ $attendanceSummary['avg_daily_hours'] }}</td>
            <td class="info-label">Most Recent Session:</td>
            <td>{{ $attendanceSummary['last_session'] }}</td>
        </tr>
    </table>

    <!-- Professional Certifications & Misconduct Records -->
    <div class="section-title">5. Professional Accreditations & Compliance</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 50%;">Accreditation / Course Title</th>
                <th style="width: 30%;">Issuing Organization</th>
                <th style="width: 20%;">Date Recorded</th>
            </tr>
        </thead>
        <tbody>
            @forelse($user->certificates as $cert)
                <tr>
                    <td style="font-weight: bold;">{{ $cert->title }}</td>
                    <td>{{ $cert->organization ?? 'Accredited Institution' }}</td>
                    <td>{{ date('d M Y', strtotime($cert->created_at)) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #94a3b8; font-style: italic;">No certifications recorded in staff profile.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Official Sign-off -->
    <table class="signoff-table">
        <tr>
            <td>
                <div class="signoff-line">Staff Member Signature</div>
            </td>
            <td>
                <div class="signoff-line">Reporting Supervisor / Unit Head</div>
            </td>
            <td>
                <div class="signoff-line">Director / Permanent Secretary</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Generated automatically by the Executive Electronic Document Management & Performance Management System (EDMS). Page 1 of 1
    </div>
</body>
</html>
