<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code — {{ $site->name }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body { overscroll-behavior-y: none !important; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .qr-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
            padding: 3rem 2.5rem;
            text-align: center;
            max-width: 420px;
            width: 100%;
            border: 2px solid #e2e8f0;
        }

        .logo-area {
            margin-bottom: 1.5rem;
        }

        .logo-area .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #166534, #15803d);
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .site-name {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.25rem;
        }

        .site-desc {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 2rem;
        }

        #qr-container {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        #qr-container canvas,
        #qr-container img {
            border-radius: 12px;
            border: 3px solid #e2e8f0;
            padding: 12px;
            background: white;
        }

        .instructions {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        .instructions p {
            font-size: 0.8rem;
            color: #166534;
            line-height: 1.6;
        }

        .instructions strong { color: #14532d; }

        .url-preview {
            font-size: 0.7rem;
            color: #94a3b8;
            word-break: break-all;
            font-family: monospace;
            margin-bottom: 1.5rem;
        }

        .actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.6rem 1.25rem;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #166534, #15803d);
            color: white;
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(22,101,52,0.3); }

        .btn-secondary {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #e2e8f0;
        }
        .btn-secondary:hover { background: #e2e8f0; }

        @media print {
            body { background: white; padding: 0; }
            .qr-card { box-shadow: none; border: none; border-radius: 0; padding: 1rem; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="qr-card">
        <div class="logo-area">
            <span class="badge">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                </svg>
                PCA Hybridization Portal
            </span>
        </div>

        <h1 class="site-name">{{ $site->name }}</h1>
        <p class="site-desc">{{ $site->description ?: 'Field Site' }}</p>

        <div id="qr-container"></div>

        <div class="instructions">
            <p>
                <strong>📱 Scan this QR code</strong> with your phone camera to quickly open the
                <strong>Monthly Harvest</strong> form for this site — no need to search!
            </p>
        </div>

        <p class="url-preview">{{ $qrUrl }}</p>

        <div class="actions">
            <button class="btn btn-primary" onclick="window.print()">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.75 12h.008v.008h-.008V12zm-2.25 0h.008v.008H16.5V12z"/>
                </svg>
                Print
            </button>
            <a href="/admin" class="btn btn-secondary">
                ← Back to Dashboard
            </a>
        </div>
    </div>

    <!-- QR Code Generator (CDN, no dependencies) -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        new QRCode(document.getElementById('qr-container'), {
            text: @json($qrUrl),
            width: 220,
            height: 220,
            colorDark: '#0f172a',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H,
        });
    </script>
</body>
</html>
