<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Minute Sheet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        h4 {
            text-align: center;
            margin-top: 0;
            color: #555;
            font-weight: normal;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        th, td {
            border: 1px solid #000;
            padding: 12px;
            vertical-align: top;
        }
        th {
            background-color: #f4f4f4;
            font-weight: bold;
            text-align: left;
        }
        .signature-img {
            max-width: 120px;
            max-height: 50px;
            display: block;
            margin-top: 10px;
        }
        .directive-text {
            white-space: pre-wrap;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <div class="header-container" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 15px;">
        <div style="width: 100%;">
            <h2>MINUTE SHEET</h2>
            <table style="width: 100%; border: none; margin-top: 15px; border-collapse: separate; border-spacing: 0 5px;">
                <tr>
                    <td style="border: none; padding: 2px; width: 15%; font-weight: bold;">Task Title:</td>
                    <td style="border: none; padding: 2px; width: 85%;">{{ $task->title ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px; font-weight: bold;">Tracking ID:</td>
                    <td style="border: none; padding: 2px;">{{ $task->tracking_id ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px; font-weight: bold;">Priority:</td>
                    <td style="border: none; padding: 2px;">
                        @if($task->priority == 1) Low @elseif($task->priority == 2) Medium @elseif($task->priority == 3) High @else Normal @endif
                    </td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px; font-weight: bold;">Created On:</td>
                    <td style="border: none; padding: 2px;">{{ $task->created_at ? $task->created_at->format('d M Y, H:i') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px; font-weight: bold;">Due Date:</td>
                    <td style="border: none; padding: 2px;">{{ $task->due_date ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 18%;">Date & Time</th>
                <th style="width: 25%;">Officer / Action</th>
                <th style="width: 57%;">Minute / Directive</th>
            </tr>
        </thead>
        <tbody>
            @foreach($minutes as $minute)
            <tr>
                <td>{{ $minute->created_at->format('d M Y, H:i') }}</td>
                <td>
                    <strong>{{ $minute->user->first_name }} {{ $minute->user->last_name }}</strong><br>
                    @if($minute->role)
                    <span style="font-size: 11px; font-weight: bold; color: #1e40af; text-transform: uppercase;">{{ $minute->role }}</span><br>
                    @endif
                    @if($minute->user->department)
                    <span style="font-size: 11px; color: #777;">{{ $minute->user->department->title ?? '' }}</span><br>
                    @endif
                    <span style="font-size: 12px; color: #333; display: inline-block; margin-top: 4px;">{{ $minute->action }}</span>
                </td>
                <td>
                    <div class="directive-text">{{ $minute->remark ?: ($minute->directive ?: 'No remark provided.') }}</div>
                    
                    @if($minute->skip_reason)
                    <div style="background-color: #fef2f2; border-left: 3px solid #ef4444; padding: 6px 10px; margin-bottom: 10px; font-size: 12px; color: #991b1b;">
                        <strong>Level Skipped Reason:</strong> {{ $minute->skip_reason }}
                    </div>
                    @endif

                    @if($minute->is_signed && ($minute->signature_path ?: $minute->user->digital_signature_path))
                        @php
                            $sigPath = $minute->signature_path ?: $minute->user->digital_signature_path;
                            $path = public_path($sigPath);
                            if(file_exists($path)) {
                                $type = pathinfo($path, PATHINFO_EXTENSION);
                                $data = file_get_contents($path);
                                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                            } else {
                                $base64 = null;
                            }
                        @endphp
                        @if($base64)
                            <div>
                                <em style="font-size: 11px; color: #555;">Digitally Signed ({{ $minute->signed_at ? $minute->signed_at->format('d/m/Y H:i') : ($minute->created_at ? $minute->created_at->format('d/m/Y H:i') : '') }})</em>
                                <img src="{{ $base64 }}" class="signature-img" />
                            </div>
                        @else
                            <div><em>[Signature Missing]</em></div>
                        @endif
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
