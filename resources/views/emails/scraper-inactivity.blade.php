<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Scraper Inactivity Alert</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            color: #334155;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
            padding: 30px 40px;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .content {
            padding: 40px;
        }
        .warning-box {
            background-color: #fff1f2;
            border-left: 4px solid #f43f5e;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 25px;
            font-size: 14px;
            line-height: 1.5;
            color: #9f1239;
            font-weight: 600;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .details-table td {
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }
        .details-table td.label {
            font-weight: 700;
            color: #64748b;
            width: 35%;
        }
        .details-table td.value {
            color: #1e293b;
            font-weight: 600;
        }
        .error-log {
            background-color: #0f172a;
            color: #f1f5f9;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.6;
            margin-bottom: 30px;
            overflow-x: auto;
        }
        .error-log label {
            display: block;
            color: #fb7185;
            font-weight: 700;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 1px;
        }
        .btn-container {
            text-align: center;
        }
        .btn {
            display: inline-block;
            background-color: #0f172a;
            color: #ffffff;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 700;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.2s;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 40px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Scraper Inactivity Alert</h1>
        </div>
        <div class="content">
            <div class="warning-box">
                স্ক্র্যাপার মনিটর সিস্টেম খেয়াল করেছে যে নিচের নিউজ সোর্সটি গত ৩ ঘণ্টা ধরে কোনো সফল স্ক্র্যাপ রেকর্ড করেনি।
            </div>

            <table class="details-table">
                <tr>
                    <td class="label">Source Name</td>
                    <td class="value">{{ $websiteName }}</td>
                </tr>
                <tr>
                    <td class="label">Source URL</td>
                    <td class="value"><a href="{{ $websiteUrl }}" style="color: #6366f1; text-decoration: none;">{{ $websiteUrl }}</a></td>
                </tr>
                <tr>
                    <td class="label">Last Attempt Time</td>
                    <td class="value">{{ $lastAttemptAt ? \Carbon\Carbon::parse($lastAttemptAt)->timezone('Asia/Dhaka')->format('Y-m-d h:i A') : 'Never' }}</td>
                </tr>
                <tr>
                    <td class="label">Last Successful Scrape</td>
                    <td class="value" style="color: #e11d48;">
                        {{ $lastScrapedAt ? \Carbon\Carbon::parse($lastScrapedAt)->timezone('Asia/Dhaka')->format('Y-m-d h:i A') : 'Never' }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Inactive For</td>
                    <td class="value" style="color: #e11d48;">{{ $inactiveDuration }}</td>
                </tr>
            </table>

            @if($lastError)
            <div class="error-log">
                <label>Last Captured Scraper Error Detail</label>
                <strong>Strategy:</strong> {{ $lastStrategy ?? 'Unknown' }}<br>
                <strong>HTTP Code:</strong> {{ $lastHttpCode ?? 'N/A' }}<br>
                <strong>Error Message:</strong> {{ $lastError }}
            </div>
            @endif

            <div class="btn-container">
                <a href="{{ $dashboardUrl }}" class="btn" style="color: #ffffff;">Open Scraper Monitor</a>
            </div>
        </div>
        <div class="footer">
            This is an automated system alert sent from your Scrapper Platform. Please inspect the source settings or proxy.
        </div>
    </div>
</body>
</html>
