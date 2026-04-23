<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign In') — SKIPS Academic Tracker</title>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon_io/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon_io/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon_io/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('favicon_io/site.webmanifest') }}">
    <link rel="shortcut icon" href="{{ asset('favicon_io/favicon.ico') }}">
    
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .auth-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        /* Left — brand panel */
        .auth-brand {
            background: var(--teal-dark);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 60px 64px;
            position: relative;
            overflow: hidden;
        }

        .auth-brand::before {
            content: '';
            position: absolute;
            top: -120px; right: -120px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(251,186,0,0.15) 0%, transparent 65%);
            pointer-events: none;
        }

        .auth-brand::after {
            content: '';
            position: absolute;
            bottom: -80px; left: -80px;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(0,106,143,0.35) 0%, transparent 65%);
            pointer-events: none;
        }

        .auth-brand-logo {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 52px;
            position: relative;
            z-index: 1;
        }

        .auth-brand-logo img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .auth-brand-logo span {
            font-family: var(--font-display);
            font-size: 1.1rem;
            color: var(--white);
            line-height: 1.2;
        }

        .auth-brand-logo small {
            display: block;
            font-family: var(--font-body);
            font-size: 0.68rem;
            color: rgba(255,255,255,0.45);
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .auth-brand-headline {
            font-family: var(--font-display);
            font-size: 2.4rem;
            color: var(--white);
            line-height: 1.2;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .auth-brand-headline em {
            color: var(--gold);
            font-style: italic;
        }

        .auth-brand-sub {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.55);
            line-height: 1.7;
            max-width: 340px;
            position: relative;
            z-index: 1;
        }

        .auth-brand-features {
            margin-top: 44px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            position: relative;
            z-index: 1;
        }

        .auth-brand-feature {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.84rem;
            color: rgba(255,255,255,0.65);
        }

        .auth-brand-feature-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--gold);
            flex-shrink: 0;
        }

        /* Right — form panel */
        .auth-form-panel {
            background: var(--off-white);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 32px;
        }

        .auth-form-card {
            width: 100%;
            max-width: 420px;
        }

        .auth-form-header {
            margin-bottom: 32px;
        }

        .auth-form-header h1 {
            font-size: 1.7rem;
            margin-bottom: 8px;
        }

        .auth-form-header p {
            font-size: 0.875rem;
            color: var(--gray-400);
        }

        .auth-divider {
            height: 1px;
            background: var(--gray-100);
            margin: 24px 0;
        }

        @media (max-width: 768px) {
            .auth-shell { grid-template-columns: 1fr; }
            .auth-brand { display: none; }
            .auth-form-panel { padding: 32px 20px; }
        }
    </style>
</head>
<body style="background: var(--off-white)">

<div class="auth-shell">

    {{-- Brand panel --}}
    <div class="auth-brand">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: left; margin-bottom: 1rem;">
                <div style="position: relative; padding: 0.75rem; border-radius: 0.75rem; backdrop-filter: blur(10px); background: rgba(255,255,255,0.3); box-shadow: 0 0 0 1px rgba(255,255,255,0.2), 0 8px 32px rgba(0,0,0,0.3);">
                    <img src="{{ asset('images/logo/logo.png') }}" alt="SKIPS Logo" 
                        style="height: 5rem; width: auto; object-fit: contain; display: block;">
                </div>
            </div>
        </div>
        <h1 class="auth-brand-headline">
            Academic<br><em>Activity</em><br>Tracker
        </h1>
        <p class="auth-brand-sub">
            A unified platform for tracking, planning and reporting academic sessions across all programmes and batches.
        </p>
        <div class="auth-brand-features">
            <div class="auth-brand-feature"><div class="auth-brand-feature-dot"></div>Weekly session planning with smart week-type rules</div>
            <div class="auth-brand-feature"><div class="auth-brand-feature-dot"></div>Cumulative variance tracking per course</div>
            <div class="auth-brand-feature"><div class="auth-brand-feature-dot"></div>One-click Excel report generation</div>
            <div class="auth-brand-feature"><div class="auth-brand-feature-dot"></div>Role-based access for Admin &amp; Staff</div>
        </div>
    </div>

    {{-- Form panel --}}
    <div class="auth-form-panel">
        <div class="auth-form-card">
            @yield('content')
        </div>
    </div>

</div>

@stack('scripts')
</body>
</html>