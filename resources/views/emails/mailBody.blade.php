<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f9f9f9; }
        .container { max-width: 600px; margin: 20px auto; padding: 30px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        .logo { max-width: 150px; }
        .content { margin-bottom: 30px; }
        .footer { font-size: 12px; color: #888; text-align: center; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <!-- Dynamic EDMS Logo from public/images -->
            <img src="{{ rtrim(config('app.url'), '/') }}/images/logo.svg" alt="{{ config('app.name', 'EDMS') }} Logo" class="logo">
        </div>
        
        <div class="content">
            <h2 style="color: #2b354f; margin-top: 0;">{{ $data['subject'] ?? '' }}</h2>
            
            <div style="font-size: 15px; color: #444; margin-top: 20px;">
                {!! $data['body'] ?? '' !!}
            </div>
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name', 'EDMS') }}. All rights reserved.<br>
            <span style="font-size: 11px;">This email was sent securely via EDMS.</span>
        </div>
    </div>
</body>
</html>
