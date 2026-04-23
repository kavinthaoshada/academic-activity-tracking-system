<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 — Page Not Found</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon_io/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon_io/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon_io/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('favicon_io/site.webmanifest') }}">
    <link rel="shortcut icon" href="{{ asset('favicon_io/favicon.ico') }}">

    <style>
        body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:var(--off-white); }
        .error-card { text-align:center; max-width:420px; padding:48px 32px; }
        .error-code { font-family:var(--font-display); font-size:5rem; color:var(--gray-100); line-height:1; margin-bottom:8px; }
        .error-icon { width:72px; height:72px; border-radius:50%; background:var(--teal-pale); display:flex; align-items:center; justify-content:center; margin:0 auto 20px; }
    </style>
</head>
<body>
<div class="error-card">
    <div class="error-code">404</div>
    <div class="error-icon">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="var(--teal)" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    </div>
    <h2 style="margin-bottom:10px">Page Not Found</h2>
    <p style="margin-bottom:28px">The page you're looking for doesn't exist or may have been moved.</p>
    <a href="{{ route('dashboard') }}" class="btn btn-teal">← Back to Dashboard</a>
</div>
</body>
</html>