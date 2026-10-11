<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14pt; line-height: 1.6; color: #1d2939; margin: 0; padding: 0; }
        .wrapper { width: 100%; background: #f4f6f4; padding: 2rem 1rem; }
        .container { max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(13,40,25,0.08); }
        .header { background: #1f7a45; color: #ffffff; padding: 1.5rem 2rem; }
        .header .logo { font-size: 22pt; font-weight: 700; letter-spacing: 0.5px; }
        .header .logo span { color: #fb6514; }
        .header .subtitle { font-size: 10pt; opacity: 0.85; margin-top: 0.25rem; }
        .body { padding: 2rem; }
        .body h1 { font-size: 18pt; color: #1d2939; margin: 0 0 0.75rem; }
        .body p { margin: 0 0 1rem; color: #344054; }
        .badge { display: inline-block; padding: 0.2rem 0.7rem; border-radius: 9999px; font-size: 9pt; font-weight: 600; text-transform: capitalize; }
        .badge-brand { background: #ecfdf3; color: #027a48; }
        .badge-warning { background: #fffbeb; color: #b54708; }
        .badge-success { background: #ecfdf3; color: #027a48; }
        .badge-error { background: #fef2f2; color: #b42318; }
        .card { background: #f9fafb; border: 1px solid #e4e7ec; border-radius: 8px; padding: 1rem; margin: 1rem 0; }
        .meta { display: flex; flex-wrap: wrap; gap: 0.5rem 1.5rem; font-size: 10pt; color: #667085; margin-top: 1rem; }
        .meta dt { display: inline; font-weight: 600; color: #344054; margin-right: 0.25rem; }
        .cta { display: inline-block; background: #1f7a45; color: #ffffff; text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; font-size: 12pt; margin-top: 0.5rem; }
        .footer { padding: 1.5rem 2rem; background: #f9fafb; border-top: 1px solid #e4e7ec; font-size: 9pt; color: #667085; text-align: center; }
        .footer a { color: #1f7a45; text-decoration: underline; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="container">
        <div class="header">
            <div class="logo">Solar<span>Share</span></div>
            <div class="subtitle">{{ __('Platform notifications') }}</div>
        </div>
        <div class="body">
            <h1>{{ $title }}</h1>
            <p>{{ $body }}</p>

            @if($actionUrl && $actionLabel)
                <a class="cta" href="{{ $actionUrl }}">{{ $actionLabel }}</a>
            @endif

            <div class="card">
                <p style="margin:0; color:#667085; font-size:10pt;">
                    {{ __('This is an automated notification from the SolarShare platform.') }}
                    {{ __('You can manage your notification preferences from your account settings.') }}
                </p>
            </div>
        </div>
        <div class="footer">
            <p>{{ now()->format('d/m/Y H:i') }} · {{ __('SolarShare Platform') }}</p>
            <p><a href="mailto:{{ $notifiable->email }}">{{ $notifiable->email }}</a></p>
        </div>
    </div>
</div>
</body>
</html>