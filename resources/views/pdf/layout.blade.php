<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'SolarShare Document' }}</title>
    <style>
        @page {
            margin: 2cm;
            @bottom-center {
                content: "Page " counter(page) " of " counter(pages);
                font-size: 10pt;
                color: #667085;
            }
        }
        
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #1d2939;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #1f7a45;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        
        .logo {
            font-size: 24pt;
            font-weight: 700;
            color: #1f7a45;
        }
        
        .logo span {
            color: #fb6514;
        }
        
        .document-title {
            text-align: right;
        }
        
        .document-title h1 {
            font-size: 18pt;
            margin: 0;
            color: #1d2939;
        }
        
        .document-title .number {
            font-size: 14pt;
            color: #667085;
            margin-top: 0.5rem;
        }
        
        .section {
            margin-bottom: 1.5rem;
        }
        
        .section-title {
            font-size: 12pt;
            font-weight: 600;
            color: #1f7a45;
            border-bottom: 1px solid #e4e7ec;
            padding-bottom: 0.25rem;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem 1.5rem;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 9pt;
            color: #667085;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            font-weight: 500;
            color: #1d2939;
        }
        
        .terms-box {
            background: #f9fafb;
            border: 1px solid #e4e7ec;
            border-radius: 8px;
            padding: 1rem;
            white-space: pre-wrap;
            font-size: 10pt;
            line-height: 1.6;
        }
        
        .amount-box {
            background: #edf7f0;
            border: 1px solid #b5dec2;
            border-radius: 8px;
            padding: 1rem;
        }
        
        .amount-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #b5dec2;
        }
        
        .amount-row:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 12pt;
            color: #1f7a45;
        }
        
        .amount-label {
            color: #344054;
        }
        
        .amount-value {
            font-weight: 600;
        }
        
        .footer {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #e4e7ec;
            text-align: center;
            font-size: 9pt;
            color: #667085;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 9pt;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .status-draft { background: #fef3f2; color: #b42318; }
        .status-signed { background: #ecfdf3; color: #027a48; }
        .status-terminated { background: #fef0c7; color: #b54708; }
        
        .status-pending { background: #fffbfa; color: #dc6803; }
        .status-confirmed { background: #ecfdf3; color: #027a48; }
        .status-cancelled { background: #fef3f2; color: #b42318; }
        .status-paid { background: #ecfdf3; color: #027a48; }
        .status-unpaid { background: #fef3f2; color: #b42318; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Solar<span>Share</span></div>
        <div class="document-title">
            <h1>@yield('documentTitle')</h1>
            <div class="number">@yield('documentNumber')</div>
        </div>
    </div>

    @yield('content')

    <div class="footer">
        <p>Generated on {{ now()->format('d/m/Y H:i') }} · SolarShare Platform</p>
        <p>This document was generated electronically and is valid without signature.</p>
    </div>
</body>
</html>