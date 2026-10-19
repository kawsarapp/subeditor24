<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $alertTitle }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #0f172a;
            margin: 0;
            padding: 20px;
            color: #334155;
        }
        .container {
            max-width: 620px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        }
        .header {
            background: {{ $severity === 'critical' ? 'linear-gradient(135deg, #b91c1c 0%, #991b1b 100%)' : ($severity === 'warning' ? 'linear-gradient(135deg, #d97706 0%, #b45309 100%)' : 'linear-gradient(135deg, #e11d48 0%, #be123c 100%)') }};
            padding: 28px 36px;
            color: #ffffff;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.25);
            margin-bottom: 8px;
        }
        .header h1 {
            margin: 0;
            font-size: 19px;
            font-weight: 800;
            line-height: 1.3;
        }
        .content {
            padding: 32px 36px;
        }
        .alert-box {
            background-color: {{ $severity === 'warning' ? '#fffbeb' : '#fff1f2' }};
            border-left: 4px solid {{ $severity === 'warning' ? '#f59e0b' : '#f43f5e' }};
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            line-height: 1.6;
            color: {{ $severity === 'warning' ? '#92400e' : '#9f1239' }};
            font-weight: 600;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .details-table td {
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }
        .details-table td.label {
            font-weight: 700;
            color: #64748b;
            width: 32%;
        }
        .details-table td.value {
            color: #0f172a;
            font-weight: 600;
            word-break: break-word;
        }
        .code-box {
            background: #0f172a;
            color: #38bdf8;
            padding: 12px 16px;
            border-radius: 8px;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 12px;
            overflow-x: auto;
            max-height: 160px;
            white-space: pre-wrap;
            word-break: break-all;
            margin-top: 6px;
        }
        .button {
            display: inline-block;
            background: #4f46e5;
            color: #ffffff !important;
            padding: 12px 28px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            margin-top: 10px;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        .footer {
            background-color: #f8fafc;
            padding: 18px 36px;
            font-size: 12px;
            color: #64748b;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="badge">{{ strtoupper($severity) }} • {{ strtoupper($category) }}</span>
            <h1>{{ $alertTitle }}</h1>
        </div>
        
        <div class="content">
            <div class="alert-box">
                {{ $alertMessage }}
            </div>

            <table class="details-table">
                <tr>
                    <td class="label">ক্যাটেগরি (Category):</td>
                    <td class="value">{{ ucfirst($category) }}</td>
                </tr>
                <tr>
                    <td class="label">তীব্রতা (Severity):</td>
                    <td class="value">
                        <span style="color: {{ $severity === 'critical' ? '#dc2626' : ($severity === 'warning' ? '#d97706' : '#e11d48') }}; font-weight: 800;">
                            {{ strtoupper($severity) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="label">সময় (Timestamp):</td>
                    <td class="value">{{ now()->setTimezone('Asia/Dhaka')->format('d M, Y - h:i A (T)') }}</td>
                </tr>

                @if(!empty($context))
                    @foreach($context as $key => $val)
                        @if($key !== 'trace' && !is_array($val) && !is_object($val))
                            <tr>
                                <td class="label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</td>
                                <td class="value">{{ $val }}</td>
                            </tr>
                        @endif
                    @endforeach

                    @if(!empty($context['trace']))
                        <tr>
                            <td class="label" style="vertical-align: top;">টেকনিক্যাল ডায়াগনস্টিক:</td>
                            <td class="value">
                                <div class="code-box">{{ is_string($context['trace']) ? Str::limit($context['trace'], 800) : json_encode($context['trace'], JSON_PRETTY_PRINT) }}</div>
                            </td>
                        </tr>
                    @endif
                @endif
            </table>

            @if(!empty($actionUrl))
                <div style="text-align: center; margin-top: 20px;">
                    <a href="{{ $actionUrl }}" class="button" target="_blank">ভিউ করুন / সমস্যা সমাধান করুন &rarr;</a>
                </div>
            @endif
        </div>

        <div class="footer">
            <p style="margin: 0;">স্বয়ংক্রিয় সিস্টেম মনিটরিং সতর্কতা • <strong>{{ $appName }}</strong></p>
            <p style="margin: 4px 0 0; font-size: 11px; color: #94a3b8;">সিস্টেমে কোনো সমস্যা সাইলেন্টলি ফেইল না হওয়ার জন্য এই নোটিফিকেশন পাঠানো হয়েছে।</p>
        </div>
    </div>
</body>
</html>
