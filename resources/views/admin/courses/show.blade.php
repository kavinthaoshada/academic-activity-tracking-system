@extends('layouts.app')
@section('title', $course->name)
@section('page-title', 'Course Detail')
@section('breadcrumb')
    <a href="{{ route('admin.courses.index') }}">Courses</a>
    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    <span>{{ Str::limit($course->name, 30) }}</span>
@endsection

@section('content')

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">

    {{-- Left: course info + sessions history --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        {{-- Info card --}}
        <div class="card">
            <div style="padding:20px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
                <div>
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
                        <span class="badge {{ $course->type === 'theory' ? 'badge-teal' : 'badge-gold' }}">{{ ucfirst($course->type) }}</span>
                        @if($course->code)<span class="badge badge-gray">{{ $course->code }}</span>@endif
                        <span class="badge {{ $course->is_active ? 'badge-success' : 'badge-gray' }}">{{ $course->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <h2 style="font-size:1.3rem">{{ $course->name }}</h2>
                    <p style="font-size:.84rem;color:var(--gray-400);margin-top:4px">{{ $course->batch->full_label }}</p>
                </div>
                <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-teal">Edit Course</a>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);border-top:1px solid var(--gray-100)">
                @foreach([
                    ['Total Hours',    $course->total_hours . ' hrs'],
                    ['Weekly Target',  $course->weekly_target . ' / week'],
                    ['Credit Hours',   $course->credit_hours ?? '—'],
                ] as [$lbl, $val])
                <div style="padding:16px 20px;{{ !$loop->last ? 'border-right:1px solid var(--gray-100)' : '' }};text-align:center">
                    <div style="font-size:.68rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--gray-400);margin-bottom:5px">{{ $lbl }}</div>
                    <div style="font-family:var(--font-display);font-size:1.2rem;color:var(--teal-dark)">{{ $val }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Session history --}}
        <div class="card">
            <div class="card-header">
                <h3>Session History</h3>
                <span class="badge badge-gray">{{ $course->weeklySessions->count() }} entries</span>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Week</th>
                            <th>Dates</th>
                            <th>Planned</th>
                            <th>Actual</th>
                            <th>Wk Var</th>
                            <th>Cu. Target</th>
                            <th>Cu. Planned</th>
                            <th>Cu. Actual</th>
                            <th>Cu. Var</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($course->weeklySessions->sortBy('academicWeek.week_number') as $s)
                        @php
                            $wv = $s->actual_sessions - $s->planned_sessions;
                            $cv = $s->cumulative_actual - $s->cumulative_planned;
                        @endphp
                        <tr>
                            <td>
                                <span style="font-weight:600;color:var(--teal)">Wk {{ $s->academicWeek->week_number }}</span>
                                <span class="week-chip {{ $s->academicWeek->week_type }}" style="margin-left:5px;font-size:.62rem">{{ $s->academicWeek->week_type }}</span>
                            </td>
                            <td style="font-size:.78rem;color:var(--gray-400)">
                                {{ $s->academicWeek->start_date->format('d M') }}–{{ $s->academicWeek->end_date->format('d M') }}
                            </td>
                            <td>{{ $s->planned_sessions }}</td>
                            <td>{{ $s->actual_sessions }}</td>
                            <td><span class="{{ $wv > 0 ? 'variance-pos' : ($wv < 0 ? 'variance-neg' : 'variance-zero') }}">{{ $wv > 0 ? '+' : '' }}{{ $wv }}</span></td>
                            <td>{{ $s->cumulative_target }}</td>
                            <td>{{ $s->cumulative_planned }}</td>
                            <td>{{ $s->cumulative_actual }}</td>
                            <td><span class="{{ $cv > 0 ? 'variance-pos' : ($cv < 0 ? 'variance-neg' : 'variance-zero') }}">{{ $cv > 0 ? '+' : '' }}{{ $cv }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="9" style="text-align:center;padding:28px;color:var(--gray-400)">No session data logged yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right: faculty + cumulative summary --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        {{-- Faculty --}}
        <div class="card">
            <div class="card-header"><h3>Faculty</h3></div>
            <div style="padding:16px">
                @forelse($course->assignments as $a)
                <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--gray-50)">
                    <div class="avatar" style="width:36px;height:36px;font-size:.72rem;flex-shrink:0">{{ $a->user->initials() }}</div>
                    <div>
                        <div style="font-size:.875rem;font-weight:500;color:var(--gray-800)">{{ $a->user->name }}</div>
                        <div style="font-size:.72rem;color:var(--gray-400)">{{ $a->user->department ?? $a->user->email }}</div>
                    </div>
                </div>
                @empty
                <p style="font-size:.84rem;color:var(--gray-400);text-align:center;padding:16px 0">No faculty assigned.</p>
                @endforelse
            </div>
        </div>

        {{-- Cumulative summary --}}
        @if($course->weeklySessions->count() > 0)
        @php
            $lastSession  = $course->weeklySessions->sortByDesc('id')->first();
            $totalPlanned = $course->weeklySessions->sum('planned_sessions');
            $totalActual  = $course->weeklySessions->sum('actual_sessions');
            $compliance   = $totalPlanned > 0 ? round(($totalActual / $totalPlanned) * 100, 1) : 0;
        @endphp
        <div class="card">
            <div class="card-header"><h3>Summary</h3></div>
            <div style="padding:16px">
                <div class="info-row"><span class="info-label">Cumulative Target</span><span class="info-value" style="font-weight:600">{{ $lastSession->cumulative_target }}</span></div>
                <div class="info-row"><span class="info-label">Cumulative Planned</span><span class="info-value">{{ $lastSession->cumulative_planned }}</span></div>
                <div class="info-row"><span class="info-label">Cumulative Actual</span><span class="info-value">{{ $lastSession->cumulative_actual }}</span></div>
                <div class="info-row">
                    <span class="info-label">Cumulative Variance</span>
                    @php $cv = $lastSession->cumulative_actual - $lastSession->cumulative_planned; @endphp
                    <span class="info-value {{ $cv >= 0 ? 'variance-pos' : 'variance-neg' }}">{{ $cv > 0 ? '+' : '' }}{{ $cv }}</span>
                </div>
                <div style="margin-top:14px">
                    <div style="display:flex;justify-content:space-between;margin-bottom:5px">
                        <span style="font-size:.75rem;color:var(--gray-500)">Compliance</span>
                        <span style="font-size:.75rem;font-weight:600;color:var(--teal)">{{ $compliance }}%</span>
                    </div>
                    <div class="progress-wrap">
                        <div class="progress-bar {{ $compliance >= 85 ? 'green' : ($compliance >= 60 ? 'gold' : 'danger') }}"
                             style="width:{{ min(100,$compliance) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection