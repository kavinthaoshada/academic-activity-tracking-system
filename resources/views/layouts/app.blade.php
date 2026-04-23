<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — SKIPS Academic Tracker</title>
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon_io/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon_io/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon_io/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('favicon_io/site.webmanifest') }}">
    <link rel="shortcut icon" href="{{ asset('favicon_io/favicon.ico') }}">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>

<div class="app-shell">

    {{-- ── Sidebar ── --}}
    <aside class="sidebar" id="sidebar">

        {{-- Glow decoration --}}
        <!-- <div class="sidebar-logo">
            <img src="{{ asset('logo.png') }}" alt="SKIPS Logo">
            <div class="sidebar-logo-text">
                <span>SKIPS University</span>
                <span>Academic Tracker</span>
            </div>
        </div> -->

        <div class="navbar-brand" style="display: flex; justify-content: center; margin-top: 0.5rem; align-items:center;">
            <img src="{{ asset('images/logo/logo.png') }}" alt="SKIPS Logo" 
                 style="height: 40px; width: auto; margin-right: 0.75rem; border-radius: 4px; object-fit: contain; background: white; padding: 2px;">
            <!-- <div>
                <span class="navbar-brand-title">Academic Audit System</span>
                <span class="navbar-brand-sub">SKIPS University</span>
            </div> -->
        </div>

        <nav class="sidebar-nav">

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
                Dashboard
            </a>

            {{-- Sessions --}}
            <div class="nav-section-label">Academic</div>
            <a href="{{ route('sessions.index') }}"
               class="nav-item {{ request()->routeIs('sessions.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/><polyline points="10,9 9,9 8,9"/>
                </svg>
                Log Sessions
            </a>
            <a href="{{ route('reports.index') }}"
               class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/>
                </svg>
                Reports
            </a>

            {{-- Admin section (only visible to admins) --}}
            @if(auth()->user()->isAdmin())
            <div class="nav-section-label">Administration</div>

            <a href="{{ route('admin.programmes.index') }}"
               class="nav-item {{ request()->routeIs('admin.programmes.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                </svg>
                Programmes
            </a>

            <a href="{{ route('admin.batches.index') }}"
               class="nav-item {{ request()->routeIs('admin.batches.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Batches
            </a>

            <a href="{{ route('admin.courses.index') }}"
               class="nav-item {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                Courses
            </a>

            <a href="{{ route('admin.academic-weeks.index') }}"
               class="nav-item {{ request()->routeIs('admin.academic-weeks.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Academic Weeks
            </a>

            <div class="nav-section-label">Team</div>

            <a href="{{ route('admin.users.index') }}"
               class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Staff Members
            </a>

            <a href="{{ route('admin.invitations.index') }}"
               class="nav-item {{ request()->routeIs('admin.invitations.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                Invitations
            </a>
            @endif

        </nav>

        {{-- User footer --}}
        <div class="sidebar-footer">
            <div class="dropdown" id="userDropdown">
                <div class="sidebar-user" onclick="toggleDropdown('userDropdown')">
                    <div class="avatar">{{ auth()->user()->initials() }}</div>
                    <div class="sidebar-user-info">
                        <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                        <div class="sidebar-user-role">{{ auth()->user()->role?->name ?? 'User' }}</div>
                    </div>
                    <svg style="width:14px;height:14px;color:rgba(255,255,255,0.4)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
                <div class="dropdown-menu" style="bottom: calc(100% + 6px); top: auto; left: 0; right: 0;">
                    <a href="{{ route('profile.show') }}" class="dropdown-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M6 20v-2a6 6 0 0 1 12 0v2"/></svg>
                        My Profile
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item danger" style="width:100%;background:none;border:none;cursor:pointer;text-align:left">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </aside>

    {{-- ── Main ── --}}
    <div class="main-content">

        {{-- Topbar --}}
        <header class="topbar">
            <div class="topbar-left">
                {{-- Mobile hamburger --}}
                <button class="topbar-icon-btn" id="sidebarToggle" onclick="toggleSidebar()" style="display:none">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div>
                    <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
                    <div class="topbar-breadcrumb">
                        <a href="{{ route('dashboard') }}">Home</a>
                        @hasSection('breadcrumb')
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                            @yield('breadcrumb')
                        @endif
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                {{-- Notification bell --}}
                <div class="dropdown" id="notifDropdown">
                    <button class="topbar-icon-btn" onclick="toggleDropdown('notifDropdown')">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        @php $unread = auth()->user()->unreadNotifications->count(); @endphp
                        @if($unread > 0)
                        <span class="notif-badge">{{ $unread > 9 ? '9+' : $unread }}</span>
                        @endif
                    </button>
                    <div class="dropdown-menu notif-panel" style="min-width:320px;right:0;left:auto">
                        <div style="padding:12px 16px;border-bottom:1px solid var(--gray-100);display:flex;align-items:center;justify-content:space-between">
                            <span style="font-size:.82rem;font-weight:600;color:var(--gray-700)">Notifications</span>
                            @if($unread > 0)
                            <form method="POST" action="{{ route('notifications.markAllRead') }}">@csrf
                                <button type="submit" style="font-size:.72rem;color:var(--teal);background:none;border:none;cursor:pointer">Mark all read</button>
                            </form>
                            @endif
                        </div>
                        @forelse(auth()->user()->notifications->take(8) as $notif)
                        <div class="notif-item {{ $notif->read_at ? '' : 'unread' }}">
                            @if(!$notif->read_at)<div class="notif-dot"></div>@else<div style="width:8px;flex-shrink:0"></div>@endif
                            <div class="notif-content">
                                <div class="notif-title">{{ $notif->data['title'] ?? 'Notification' }}</div>
                                <div class="notif-msg">{{ $notif->data['message'] ?? '' }}</div>
                                <div class="notif-time">{{ $notif->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        @empty
                        <div style="padding:24px;text-align:center;color:var(--gray-400);font-size:.82rem">No notifications</div>
                        @endforelse
                    </div>
                </div>

                {{-- Avatar --}}
                <div class="avatar" style="cursor:default" title="{{ auth()->user()->name }}">
                    {{ auth()->user()->initials() }}
                </div>
            </div>
        </header>

        {{-- Flash messages --}}
        <div style="padding: 0 32px; padding-top: 0;" id="flash-wrap">
            @if(session('success'))
            <div class="alert alert-success" style="margin:16px 0 0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20,6 9,17 4,12"/></svg>
                <span class="alert-message">{{ session('success') }}</span>
                <button class="alert-close" onclick="this.closest('.alert').remove()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger" style="margin:16px 0 0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                <span class="alert-message">{{ session('error') }}</span>
                <button class="alert-close" onclick="this.closest('.alert').remove()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            @endif
            @if(session('info'))
            <div class="alert alert-info" style="margin:16px 0 0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span class="alert-message">{{ session('info') }}</span>
                <button class="alert-close" onclick="this.closest('.alert').remove()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            @endif
        </div>

        {{-- Page content --}}
        <main class="page-content">
            @yield('content')
        </main>

    </div>{{-- /main-content --}}

</div>{{-- /app-shell --}}

{{-- Mobile overlay --}}
<div id="sidebarOverlay" onclick="toggleSidebar()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:99;backdrop-filter:blur(2px)"></div>

<script>
function toggleDropdown(id) {
    const el = document.getElementById(id);
    const isOpen = el.classList.contains('open');
    document.querySelectorAll('.dropdown.open').forEach(d => d.classList.remove('open'));
    if (!isOpen) el.classList.add('open');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown.open').forEach(d => d.classList.remove('open'));
    }
});

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const open = sidebar.classList.toggle('open');
    overlay.style.display = open ? 'block' : 'none';
}

// Show hamburger on small screens
function checkResponsive() {
    const btn = document.getElementById('sidebarToggle');
    if (btn) btn.style.display = window.innerWidth <= 1024 ? 'flex' : 'none';
}
checkResponsive();
window.addEventListener('resize', checkResponsive);

// Auto-dismiss flash after 5s
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
        a.style.transition = 'opacity 0.4s';
        a.style.opacity = '0';
        setTimeout(() => a.remove(), 400);
    });
}, 5000);
</script>

@stack('scripts')
</body>
</html>