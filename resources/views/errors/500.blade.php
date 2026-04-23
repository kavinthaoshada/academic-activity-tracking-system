<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Something went wrong — SKIPS</title>
    <link rel="icon" href="/logo.png" type="image/png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap');

        :root {
            --gold:      #fbba00;
            --teal:      #004e6f;
            --teal-dark: #003750;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #fafaf8;
            color: #252520;
            -webkit-font-smoothing: antialiased;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px 16px;
        }

        /* Background decoration */
        body::before {
            content: '';
            position: fixed;
            top: -200px;
            left: 50%;
            transform: translateX(-50%);
            width: 800px;
            height: 800px;
            background: radial-gradient(ellipse at center, rgba(0, 78, 111, 0.05) 0%, transparent 65%);
            pointer-events: none;
            z-index: 0;
        }

        .error-shell {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 520px;
            text-align: center;
        }

        /* Logo strip */
        .logo-strip {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 48px;
        }

        .logo-strip img {
            width: 36px;
            height: 36px;
            object-fit: contain;
        }

        .logo-strip-text {
            font-size: 13px;
            font-weight: 600;
            color: var(--teal-dark);
            letter-spacing: 0.02em;
        }

        /* Illustration */
        .error-illustration {
            position: relative;
            margin: 0 auto 36px;
            width: 160px;
            height: 160px;
        }

        /* Outer ring — animated pulse */
        .ring-outer {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px solid rgba(0, 78, 111, 0.08);
            animation: ringPulse 3s ease-in-out infinite;
        }

        @keyframes ringPulse {
            0%, 100% { transform: scale(1);   opacity: 1; }
            50%       { transform: scale(1.06); opacity: 0.5; }
        }

        /* Inner circle */
        .ring-inner {
            position: absolute;
            inset: 20px;
            border-radius: 50%;
            background: #ffffff;
            border: 1.5px solid #eeede9;
            box-shadow: 0 8px 32px rgba(0, 78, 111, 0.08), 0 2px 8px rgba(0,0,0,0.04);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Error code watermark */
        .error-code-watermark {
            position: absolute;
            top: -20px;
            right: -20px;
            font-family: 'DM Serif Display', Georgia, serif;
            font-size: 64px;
            color: rgba(0, 78, 111, 0.06);
            line-height: 1;
            pointer-events: none;
            user-select: none;
        }

        /* Gold accent dot */
        .accent-dot {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: var(--gold);
            border: 2px solid #ffffff;
            box-shadow: 0 2px 6px rgba(251, 186, 0, 0.4);
        }

        /* Main content */
        .error-content { margin-bottom: 40px; }

        .error-eyebrow {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #9c9b94;
            margin-bottom: 14px;
        }

        .error-title {
            font-family: 'DM Serif Display', Georgia, serif;
            font-size: 2rem;
            color: var(--teal-dark);
            line-height: 1.2;
            margin-bottom: 14px;
        }

        .error-title em {
            font-style: italic;
            color: var(--gold);
        }

        .error-body {
            font-size: 15px;
            color: #737269;
            line-height: 1.75;
            max-width: 400px;
            margin: 0 auto;
        }

        /* Action buttons */
        .error-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 48px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 11px 22px;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: 1.5px solid transparent;
            transition: all 0.18s ease;
            line-height: 1;
        }

        .btn svg { width: 15px; height: 15px; flex-shrink: 0; }

        .btn-primary {
            background: var(--gold);
            color: var(--teal-dark);
            border-color: var(--gold);
            box-shadow: 0 4px 14px rgba(251, 186, 0, 0.3);
        }

        .btn-primary:hover {
            background: #d49e00;
            border-color: #d49e00;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(251, 186, 0, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: var(--teal);
            border-color: #dddcd7;
        }

        .btn-outline:hover {
            background: #f0f8fc;
            border-color: #b5d4f4;
        }

        /* Status card */
        .status-card {
            background: #ffffff;
            border: 1px solid #eeede9;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 48px;
            text-align: left;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .status-card-title {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #9c9b94;
            margin-bottom: 14px;
        }

        .status-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 0;
            border-bottom: 1px solid #f7f7f5;
            font-size: 13px;
        }

        .status-row:last-child { border-bottom: none; }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .dot-ok     { background: #1a8a4a; }
        .dot-warn   { background: #d4720a; }
        .dot-error  { background: #c0392b; }

        .status-row-label { color: #737269; flex: 1; }
        .status-row-value { font-weight: 600; color: #252520; }

        /* Footer */
        .error-footer {
            font-size: 12px;
            color: #c4c3bd;
            line-height: 1.7;
        }

        .error-footer a { color: #9c9b94; text-decoration: none; }
        .error-footer a:hover { color: var(--teal); }

        /* Retry animation */
        .retry-icon {
            display: inline-block;
            transition: transform 0.5s ease;
        }

        .btn-outline:hover .retry-icon {
            transform: rotate(180deg);
        }

        @media (max-width: 480px) {
            .error-title { font-size: 1.6rem; }
            .error-code-watermark { font-size: 44px; top: -10px; right: -10px; }
            .error-actions { flex-direction: column; align-items: center; }
            .btn { width: 100%; justify-content: center; max-width: 280px; }
        }
    </style>
</head>
<body>

<div class="error-shell">

    {{-- Logo --}}
    <div class="logo-strip">
        <img src="/logo.png" alt="SKIPS University">
        <span class="logo-strip-text">SKIPS University &nbsp;·&nbsp; Academic Tracker</span>
    </div>

    {{-- Illustration --}}
    <div class="error-illustration">
        <div class="error-code-watermark">500</div>
        <div class="ring-outer"></div>
        <div class="ring-inner">
            {{-- Server/error SVG icon --}}
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#004e6f" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="2" width="20" height="8" rx="2" ry="2"/>
                <rect x="2" y="14" width="20" height="8" rx="2" ry="2"/>
                <line x1="6" y1="6" x2="6.01" y2="6"/>
                <line x1="6" y1="18" x2="6.01" y2="18"/>
                <line x1="12" y1="12" x2="12" y2="12.01"/>
                <path d="M16 10 L16 14" stroke="#fbba00" stroke-width="1.8"/>
                <path d="M14 12 L18 12" stroke="#fbba00" stroke-width="1.8"/>
            </svg>
        </div>
        <div class="accent-dot"></div>
    </div>

    {{-- Content --}}
    <div class="error-content">
        <span class="error-eyebrow">Error 500 &nbsp;·&nbsp; Internal Server Error</span>
        <h1 class="error-title">Something went<br><em>unexpectedly</em> wrong</h1>
        <p class="error-body">
            Our server encountered an error while processing your request.
            This is not your fault — our team has been notified and is looking into it.
            Please try again in a few moments.
        </p>
    </div>

    {{-- Actions --}}
    <div class="error-actions">
        <a href="javascript:window.location.reload()" class="btn btn-outline">
            <span class="retry-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1,4 1,10 7,10"/><path d="M3.51 15a9 9 0 1 0 .49-3.95"/></svg>
            </span>
            Try Again
        </a>
        <a href="/" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
            Go to Dashboard
        </a>
    </div>

    {{-- System status card --}}
    <div class="status-card">
        <div class="status-card-title">System Status</div>
        <div class="status-row">
            <div class="status-dot dot-ok"></div>
            <span class="status-row-label">Application</span>
            <span class="status-row-value" style="color:#1a8a4a">Running</span>
        </div>
        <div class="status-row">
            <div class="status-dot dot-warn"></div>
            <span class="status-row-label">This Request</span>
            <span class="status-row-value" style="color:#d4720a">Failed</span>
        </div>
        <div class="status-row">
            <div class="status-dot dot-ok"></div>
            <span class="status-row-label">Your Session</span>
            <span class="status-row-value" style="color:#1a8a4a">Preserved</span>
        </div>
        <div class="status-row">
            <div class="status-dot dot-ok"></div>
            <span class="status-row-label">Your Data</span>
            <span class="status-row-value" style="color:#1a8a4a">Safe</span>
        </div>
    </div>

    {{-- Footer --}}
    <div class="error-footer">
        <strong style="color:#737269">SKIPS University</strong> &nbsp;·&nbsp; School of Computer Science<br>
        If this problem persists, please contact the system administrator.<br>
        <span style="margin-top:6px;display:block">Error occurred at {{ now()->format('d M Y, H:i:s') }} IST</span>
    </div>

</div>

</body>
</html>