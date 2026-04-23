@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@php
    $user = auth()->user();

    // Stat calculations
    $totalCourses   = $user->isAdmin()
        ? \App\Models\Course::where('is_active', true)->count()
        : $user->courses()->where('is_active', true)->count();

    $totalBatches   = $user->isAdmin()
        ? \App\Models\Batch::where('is_active', true)->count()
        : \App\Models\Batch::whereHas('courses.assignments', fn($q) => $q->where('user_id', $user->id))->count();

    $sessionsThisWeek = \App\Models\WeeklySession::when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id))
        ->whereHas('academicWeek', fn($q) => $q->where('start_date', '<=', now())->where('end_date', '>=', now()))
        ->count();

    $totalPlanned  = \App\Models\WeeklySession::when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id))->sum('planned_sessions');
    $totalActual   = \App\Models\WeeklySession::when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id))->sum('actual_sessions');
    $compliance    = $totalPlanned > 0 ? round(($totalActual / $totalPlanned) * 100, 1) : 0;

    // Recent sessions for activity feed
    $recentSessions = \App\Models\WeeklySession::with(['course.batch.programme', 'academicWeek', 'user'])
        ->when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id))
        ->latest()->take(8)->get();

    // Courses with negative variance (needs attention)
    $attentionCourses = \App\Models\WeeklySession::selectRaw('course_id, SUM(cumulative_variance) as total_variance')
        ->when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id))
        ->groupBy('course_id')
        ->having('total_variance', '<', 0)
        ->with('course.batch.programme')
        ->orderBy('total_variance')
        ->take(5)->get();

    // Weekly progress data for chart (last 8 weeks)
    $chartWeeks = \App\Models\AcademicWeek::whereHas('weeklySessions', function($q) use ($user) {
        if (!$user->isAdmin()) $q->where('user_id', $user->id);
    })->orderByDesc('week_number')->take(8)->get()->reverse();

    $chartLabels  = $chartWeeks->pluck('week_number')->map(fn($w) => "Wk $w")->values()->toJson();
    $chartPlanned = $chartLabels ? $chartWeeks->map(fn($w) =>
        \App\Models\WeeklySession::where('academic_week_id', $w->id)
            ->when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id))
            ->sum('planned_sessions')
    )->values()->toJson() : '[]';
    $chartActual  = $chartLabels ? $chartWeeks->map(fn($w) =>
        \App\Models\WeeklySession::where('academic_week_id', $w->id)
            ->when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id))
            ->sum('actual_sessions')
    )->values()->toJson() : '[]';

    $pendingInvitations = $user->isAdmin()
        ? \App\Models\StaffInvitation::whereNull('accepted_at')->where('expires_at', '>', now())->count()
        : 0;
@endphp

{{-- Stats grid --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon teal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $totalCourses }}</div>
            <div class="stat-label">{{ $user->isAdmin() ? 'Total Courses' : 'My Courses' }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon gold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $totalBatches }}</div>
            <div class="stat-label">Active Batches</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $compliance }}%</div>
            <div class="stat-label">Overall Compliance</div>
            <div class="stat-change {{ $compliance >= 85 ? 'up' : 'down' }}">
                {{ $compliance >= 85 ? '↑ On track' : '↓ Below target' }}
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon {{ $attentionCourses->count() > 0 ? 'red' : 'green' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $attentionCourses->count() }}</div>
            <div class="stat-label">Courses Behind</div>
            @if($attentionCourses->count() > 0)
            <div class="stat-change down">↓ Needs attention</div>
            @else
            <div class="stat-change up">↑ All on track</div>
            @endif
        </div>
    </div>
    @if($user->isAdmin() && $pendingInvitations > 0)
    <div class="stat-card">
        <div class="stat-icon gold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $pendingInvitations }}</div>
            <div class="stat-label">Pending Invitations</div>
        </div>
    </div>
    @endif
</div>

{{-- Main grid: Chart + Attention --}}
<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;margin-bottom:20px">

    {{-- Planned vs Actual chart --}}
    <div class="card">
        <div class="card-header">
            <h3>Sessions — Planned vs Actual (last 8 weeks)</h3>
            <span class="badge badge-teal">Current semester</span>
        </div>
        <div class="card-body" style="padding:20px 24px 16px">
            <canvas id="sessionsChart" height="220"></canvas>
        </div>
    </div>

    {{-- Courses needing attention --}}
    <div class="card">
        <div class="card-header">
            <h3>Needs Attention</h3>
            @if($attentionCourses->count() > 0)
            <span class="badge badge-danger">{{ $attentionCourses->count() }} courses</span>
            @endif
        </div>
        <div style="max-height:310px;overflow-y:auto">
            @forelse($attentionCourses as $session)
            @php $course = $session->course; @endphp
            <div style="padding:12px 20px;border-bottom:1px solid var(--gray-50);display:flex;align-items:center;justify-content:space-between;gap:10px">
                <div style="min-width:0">
                    <div style="font-size:.82rem;font-weight:500;color:var(--gray-800);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        {{ $course->name }}
                    </div>
                    <div style="font-size:.72rem;color:var(--gray-400);margin-top:2px">
                        {{ $course->batch->full_label }}
                    </div>
                </div>
                <span class="badge badge-danger">{{ $session->total_variance }}</span>
            </div>
            @empty
            <div class="empty-state" style="padding:30px 20px">
                <div class="empty-icon" style="width:44px;height:44px;margin-bottom:12px">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20,6 9,17 4,12"/></svg>
                </div>
                <p style="font-size:.82rem">All courses are on track!</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- Bottom: Recent activity + Quick actions --}}
<div style="display:grid;grid-template-columns:1fr 320px;gap:20px">

    {{-- Recent sessions --}}
    <div class="card">
        <div class="card-header">
            <h3>Recent Session Entries</h3>
            <a href="{{ route('sessions.index') }}" class="btn btn-ghost btn-sm">View all</a>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Week</th>
                        <th>Planned</th>
                        <th>Actual</th>
                        <th>Variance</th>
                        @if($user->isAdmin())<th>Faculty</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSessions as $s)
                    <tr>
                        <td>
                            <div style="font-weight:500;color:var(--gray-800);font-size:.84rem">{{ Str::limit($s->course->name, 28) }}</div>
                            <div style="font-size:.72rem;color:var(--gray-400)">{{ $s->course->batch->programme->code ?? '' }} · {{ ucfirst($s->course->type) }}</div>
                        </td>
                        <td><span style="font-size:.8rem;color:var(--gray-600)">Wk {{ $s->academicWeek->week_number }}</span></td>
                        <td>{{ $s->planned_sessions }}</td>
                        <td>{{ $s->actual_sessions }}</td>
                        <td>
                            @php $v = $s->actual_sessions - $s->planned_sessions; @endphp
                            <span class="{{ $v > 0 ? 'variance-pos' : ($v < 0 ? 'variance-neg' : 'variance-zero') }}">
                                {{ $v > 0 ? '+' : '' }}{{ $v }}
                            </span>
                        </td>
                        @if($user->isAdmin())
                        <td style="font-size:.8rem;color:var(--gray-500)">{{ $s->user->name }}</td>
                        @endif
                    </tr>
                    @empty
                    <tr><td colspan="{{ $user->isAdmin() ? 6 : 5 }}" style="text-align:center;color:var(--gray-400);padding:32px">No session entries yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Quick actions --}}
    <div style="display:flex;flex-direction:column;gap:14px">
        <div class="card">
            <div class="card-header"><h3>Quick Actions</h3></div>
            <div style="padding:16px;display:flex;flex-direction:column;gap:8px">
                <a href="{{ route('sessions.create') }}" class="btn btn-primary" style="justify-content:flex-start">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Log Sessions
                </a>
                <a href="{{ route('reports.index') }}" class="btn btn-teal" style="justify-content:flex-start">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Generate Report
                </a>
                @if($user->isAdmin())
                <a href="{{ route('admin.invitations.index') }}" class="btn btn-outline" style="justify-content:flex-start">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    Invite Staff
                </a>
                <a href="{{ route('admin.courses.create') }}" class="btn btn-outline" style="justify-content:flex-start">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                    Add Course
                </a>
                @endif
            </div>
        </div>

        {{-- Compliance ring --}}
        <div class="card">
            <div class="card-header"><h3>Compliance</h3></div>
            <div style="padding:20px;text-align:center">
                <div style="position:relative;width:110px;height:110px;margin:0 auto 14px">
                    <svg viewBox="0 0 110 110" width="110" height="110">
                        <circle cx="55" cy="55" r="46" fill="none" stroke="var(--gray-100)" stroke-width="10"/>
                        <circle cx="55" cy="55" r="46" fill="none"
                            stroke="{{ $compliance >= 85 ? 'var(--success)' : ($compliance >= 60 ? 'var(--gold)' : 'var(--danger)') }}"
                            stroke-width="10" stroke-linecap="round"
                            stroke-dasharray="{{ round(2 * 3.14159 * 46) }}"
                            stroke-dashoffset="{{ round(2 * 3.14159 * 46 * (1 - $compliance / 100)) }}"
                            transform="rotate(-90 55 55)"/>
                    </svg>
                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center">
                        <span style="font-family:var(--font-display);font-size:1.4rem;color:var(--teal-dark);line-height:1">{{ $compliance }}%</span>
                        <span style="font-size:.62rem;color:var(--gray-400);text-transform:uppercase;letter-spacing:.06em">Overall</span>
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:.75rem;color:var(--gray-500)">
                    <span>Planned: <strong>{{ $totalPlanned }}</strong></span>
                    <span>Actual: <strong>{{ $totalActual }}</strong></span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('sessionsChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! $chartLabels !!},
        datasets: [
            {
                label: 'Planned',
                data: {!! $chartPlanned !!},
                backgroundColor: 'rgba(0,78,111,0.15)',
                borderColor: 'rgba(0,78,111,0.6)',
                borderWidth: 1.5,
                borderRadius: 4,
            },
            {
                label: 'Actual',
                data: {!! $chartActual !!},
                backgroundColor: 'rgba(251,186,0,0.7)',
                borderColor: 'rgba(212,158,0,1)',
                borderWidth: 1.5,
                borderRadius: 4,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top', labels: { font: { family: 'DM Sans' }, boxWidth: 12, usePointStyle: true } },
            tooltip: { bodyFont: { family: 'DM Sans' }, titleFont: { family: 'DM Sans' } }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { family: 'DM Sans', size: 11 } } },
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { family: 'DM Sans', size: 11 }, stepSize: 1 } }
        }
    }
});
</script>
@endpush