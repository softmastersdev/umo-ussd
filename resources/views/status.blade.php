<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UmoPay USSD Gateway</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 2.5rem;
            width: 100%;
            max-width: 540px;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
        }

        .header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .logo {
            width: 42px;
            height: 42px;
            background: #0f172a;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #fff;
            font-weight: 700;
            letter-spacing: -1px;
            flex-shrink: 0;
        }

        .header-text h1 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .header-text p {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 1px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            margin-left: auto;
            white-space: nowrap;
        }

        .status-badge .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #22c55e;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .4; }
        }

        .section-label {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: .75rem;
        }

        .endpoints {
            display: flex;
            flex-direction: column;
            gap: .5rem;
            margin-bottom: 1.75rem;
        }

        .endpoint {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .65rem .9rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .method {
            font-size: 0.7rem;
            font-weight: 700;
            color: #fff;
            background: #3b82f6;
            padding: 2px 7px;
            border-radius: 4px;
            flex-shrink: 0;
        }

        .path {
            font-family: 'SFMono-Regular', Consolas, monospace;
            font-size: 0.82rem;
            color: #0f172a;
            flex: 1;
        }

        .provider {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .copy-btn {
            background: none;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 3px 8px;
            font-size: 0.7rem;
            color: #64748b;
            cursor: pointer;
            flex-shrink: 0;
            transition: background .15s, color .15s, border-color .15s;
        }

        .copy-btn:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        .copy-btn.copied {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #15803d;
        }

        .meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .5rem;
        }

        .meta-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: .6rem .9rem;
        }

        .meta-item .label {
            font-size: 0.7rem;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .meta-item .value {
            font-size: 0.85rem;
            font-weight: 600;
            color: #0f172a;
        }

        .db-status { color: #15803d; }
        .db-error  { color: #dc2626; }

        footer {
            margin-top: 1.5rem;
            font-size: 0.72rem;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="logo">U</div>
            <div class="header-text">
                <h1>UmoPay USSD Gateway</h1>
                <p>{{ config('app.env') }} &mdash; {{ config('app.url') }}</p>
            </div>
            <span class="status-badge">
                <span class="dot"></span>
                Operational
            </span>
        </div>

        <p class="section-label">Endpoints</p>
        <div class="endpoints">
            <div class="endpoint">
                <span class="method">POST</span>
                <span class="path">{{ url('/ussd/at') }}</span>
                <span class="provider">Africa's Talking</span>
                <button class="copy-btn" onclick="copyUrl(this, '{{ url('/ussd/at') }}')">Copy</button>
            </div>
            <div class="endpoint">
                <span class="method">POST</span>
                <span class="path">{{ url('/ussd/nalo') }}</span>
                <span class="provider">Nalo Solutions</span>
                <button class="copy-btn" onclick="copyUrl(this, '{{ url('/ussd/nalo') }}')">Copy</button>
            </div>
            <div class="endpoint">
                <span class="method">POST</span>
                <span class="path">{{ url('/ussd/arkesel') }}</span>
                <span class="provider">Arkesel</span>
                <button class="copy-btn" onclick="copyUrl(this, '{{ url('/ussd/arkesel') }}')">Copy</button>
            </div>
            <div class="endpoint">
                <span class="method">GET</span>
                <span class="path">{{ url('/health') }}</span>
                <span class="provider">JSON status</span>
                <button class="copy-btn" onclick="copyUrl(this, '{{ url('/health') }}')">Copy</button>
            </div>
        </div>

        <p class="section-label">System</p>
        <div class="meta">
            <div class="meta-item">
                <div class="label">PHP</div>
                <div class="value">{{ PHP_MAJOR_VERSION }}.{{ PHP_MINOR_VERSION }}</div>
            </div>
            <div class="meta-item">
                <div class="label">Laravel</div>
                <div class="value">{{ app()->version() }}</div>
            </div>
            <div class="meta-item">
                <div class="label">Database</div>
                <div class="value {{ $dbOk ? 'db-status' : 'db-error' }}">
                    {{ $dbOk ? 'Connected' : 'Unavailable' }}
                </div>
            </div>
            <div class="meta-item">
                <div class="label">Server time</div>
                <div class="value">{{ now()->format('H:i T') }}</div>
            </div>
        </div>
    </div>

    <footer>Generated {{ now()->toIso8601String() }}</footer>

    <script>
        function copyUrl(btn, url) {
            navigator.clipboard.writeText(url).then(() => {
                btn.textContent = 'Copied!';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.textContent = 'Copy';
                    btn.classList.remove('copied');
                }, 2000);
            });
        }
    </script>
</body>
</html>
