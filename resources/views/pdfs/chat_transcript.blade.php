<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Chat Transcript - {{ $title }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            margin: 0;
            padding: 24px;
            line-height: 1.5;
        }
        .header {
            border-bottom: 2px solid #0284c7;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .header-title {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 4px 0;
        }
        .header-subtitle {
            font-size: 12px;
            color: #64748b;
            margin: 0 0 12px 0;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            background-color: #f8fafc;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .meta-table td {
            padding: 6px 12px;
            border: 1px solid #e2e8f0;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
            width: 25%;
        }
        .meta-value {
            color: #0f172a;
        }
        .date-divider {
            text-align: center;
            margin: 20px 0 12px 0;
        }
        .date-divider span {
            background-color: #e2e8f0;
            color: #475569;
            padding: 3px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .message-block {
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
        }
        .message-header {
            margin-bottom: 4px;
        }
        .sender-name {
            font-weight: bold;
            color: #0369a1;
            font-size: 11px;
        }
        .message-time {
            color: #94a3b8;
            font-size: 10px;
            margin-left: 6px;
        }
        .edited-tag {
            color: #94a3b8;
            font-size: 9px;
            font-style: italic;
            margin-left: 4px;
        }
        .message-body {
            color: #1e293b;
            font-size: 11.5px;
            word-wrap: break-word;
            white-space: pre-wrap;
        }
        .reply-preview {
            background-color: #f1f5f9;
            border-left: 3px solid #0284c7;
            padding: 4px 8px;
            font-size: 10px;
            color: #475569;
            margin-bottom: 4px;
            border-radius: 2px;
        }
        .attachments-list {
            margin-top: 6px;
            font-size: 10px;
        }
        .attachment-item {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-right: 6px;
            margin-top: 4px;
            color: #0369a1;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="header-title">EDMS Official Chat Transcript</h1>
        <p class="header-subtitle">Electronic Document Management System &mdash; Certified Communication Log</p>

        <table class="meta-table">
            <tr>
                <td class="meta-label">Conversation:</td>
                <td class="meta-value">{{ $title }}</td>
                <td class="meta-label">Type:</td>
                <td class="meta-value">{{ ucfirst($type) }} Conversation</td>
            </tr>
            <tr>
                <td class="meta-label">Generated On:</td>
                <td class="meta-value">{{ $export_date }}</td>
                <td class="meta-label">Exported By:</td>
                <td class="meta-value">{{ $exported_by }}</td>
            </tr>
            <tr>
                <td class="meta-label">Total Messages:</td>
                <td class="meta-value">{{ $total_messages }}</td>
                <td class="meta-label">Participants:</td>
                <td class="meta-value">{{ $participants }}</td>
            </tr>
        </table>
    </div>

    <div class="transcript-content">
        @php $currentDate = null; @endphp
        @forelse($messages as $msg)
            @if($msg['formatted_date'] !== $currentDate)
                @php $currentDate = $msg['formatted_date']; @endphp
                <div class="date-divider">
                    <span>{{ $currentDate }}</span>
                </div>
            @endif

            <div class="message-block">
                <div class="message-header">
                    <span class="sender-name">{{ $msg['sender_name'] }}</span>
                    <span class="message-time">{{ $msg['time'] }}</span>
                    @if(!empty($msg['is_edited']))
                        <span class="edited-tag">(edited)</span>
                    @endif
                </div>

                @if(!empty($msg['reply_to']))
                    <div class="reply-preview">
                        <strong>Reply to {{ $msg['reply_to']['sender_name'] }}:</strong> {{ $msg['reply_to']['message'] }}
                    </div>
                @endif

                <div class="message-body">{{ $msg['message'] }}</div>

                @if(!empty($msg['attachments']) && count($msg['attachments']) > 0)
                    <div class="attachments-list">
                        @foreach($msg['attachments'] as $att)
                            <span class="attachment-item">
                                &#128206; {{ $att['name'] }} ({{ $att['size'] ?? 'Attachment' }})
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div style="text-align: center; color: #94a3b8; padding: 40px 0;">
                No messages recorded in this conversation.
            </div>
        @endforelse
    </div>

    <div class="footer">
        Confidential Document &bull; Generated from EDMS Chat Module &bull; {{ date('Y') }}
    </div>

</body>
</html>
