{{-- <x-mail::message>
     <div class="reply-content">
        {!! nl2br(e($reply)) !!}
    </div>

    @if(!empty($original))
    <div class="original-message">
        <p><strong>On {{ $original['date'] ?? '' }}, {{ $original['from'] ?? '' }} wrote:</strong></p>
        <blockquote>
            {!! nl2br(e($original['body'] ?? '')) !!}
        </blockquote>
    </div>
    @endif

<h2>{{$original['subject']}}</h2>
{!! $original['body'] !!} <br/>
<div style="margin:0 auto; text-align:center">
    <h1 style="padding-top:20px; padding-bottom:20px"></h1>
</div>

Thanks,<br>
{{ config('app.name')}}
</x-mail::message> --}}


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .reply-content {
            margin: 20px 0;
            white-space: pre-wrap;
        }
        .original-message {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            color: #666;
        }
        .original-header {
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        blockquote {
            margin: 10px 0;
            padding-left: 15px;
            border-left: 3px solid #ccc;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="reply-content">
        {!! $reply !!}
    </div>

    @if(!empty($original))
    <div class="original-message">
        <div class="original-header">
            <strong>On {{ $original['date'] ?? date('Y-m-d H:i:s') }}, {{ $original['from'] ?? 'Unknown' }} wrote:</strong>
        </div>
        <blockquote>
            {!! $original['body'] ?? $original['text'] ?? 'Original message' !!}
        </blockquote>
    </div>
    @endif
</body>
</html>