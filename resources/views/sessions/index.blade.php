@extends('layouts.app')
@section('title', 'Sessions')
@section('page-title', 'Session Entries')

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1>Session Entries</h1>
        <p>Track and manage all weekly session data</p>
    </div>
    <a href="{{ route('sessions.create') }}" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Log Sessions
    </a>
</div>

{{-- Filters --}}
<form method="GET" class="filter-bar">
    <select name="batch_id" class="form-control" onchange="this.form.submit()">
        <option value="">All Batches</option>
        @foreach($batches as $b)
        <option value="{{ $b->id }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>{{ $b->full_label }}</option>
        @endforeach
    </select>
    <select name="week_id" class="form-control" onchange="this.form.submit()">
        <option value="">All Weeks</option>
        @foreach($weeks as $w)
        <option value="{{ $w->id }}" {{ request('week_id') == $w->id ? 'selected' : '' }}>{{ $w->label }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-outline">Filter</button>
    <a href="{{ route('sessions.index') }}" class="btn btn-ghost">Clear</a>
</form>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Batch</th>
                    <th>Week</th>
                    <th>Planned</th>
                    <th>Actual</th>
                    <th>Variance</th>
                    <th>Cumu. Target</th>
                    <th>Cumu. Actual</th>
                    <th>Cumu. Var</th>
                    @if(auth()->user()->isAdmin())<th>Faculty</th>@endif
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $s)
                @php $cv = $s->cumulative_actual - $s->cumulative_planned; @endphp
                <tr>
                    <td>
                        <div style="font-weight:500;color:var(--gray-800)">{{ $s->course->name }}</div>
                        <span class="badge {{ $s->course->type === 'theory' ? 'badge-teal' : 'badge-gold' }}" style="margin-top:3px">{{ ucfirst($s->course->type) }}</span>
                    </td>
                    <td style="font-size:.82rem;color:var(--gray-500)">{{ $s->course->batch->full_label }}</td>
                    <td style="font-size:.82rem">Wk {{ $s->academicWeek->week_number }}</td>
                    <td>{{ $s->planned_sessions }}</td>
                    <td>{{ $s->actual_sessions }}</td>
                    <td>
                        @php $v = $s->actual_sessions - $s->planned_sessions; @endphp
                        <span class="{{ $v > 0 ? 'variance-pos' : ($v < 0 ? 'variance-neg' : 'variance-zero') }}">
                            {{ $v > 0 ? '+' : '' }}{{ $v }}
                        </span>
                    </td>
                    <td>{{ $s->cumulative_target }}</td>
                    <td>{{ $s->cumulative_actual }}</td>
                    <td>
                        <span class="{{ $cv > 0 ? 'variance-pos' : ($cv < 0 ? 'variance-neg' : 'variance-zero') }}">
                            {{ $cv > 0 ? '+' : '' }}{{ $cv }}
                        </span>
                    </td>
                    @if(auth()->user()->isAdmin())
                    <td style="font-size:.82rem;color:var(--gray-500)">{{ $s->user->name }}</td>
                    @endif
                    <td class="actions">
                        <a href="{{ route('sessions.create', ['batch_id' => $s->course->batch_id, 'week_id' => $s->academic_week_id]) }}"
                           class="btn btn-ghost btn-sm" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ auth()->user()->isAdmin() ? 11 : 10 }}" style="text-align:center;color:var(--gray-400);padding:40px">
                        No session entries found. <a href="{{ route('sessions.create') }}">Log your first session →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($sessions->hasPages())
    <div class="card-footer">
        <span style="font-size:.82rem;color:var(--gray-400)">Showing {{ $sessions->firstItem() }}–{{ $sessions->lastItem() }} of {{ $sessions->total() }}</span>
        <div class="pagination">
            @if($sessions->onFirstPage())
                <span class="page-link disabled">‹</span>
            @else
                <a href="{{ $sessions->previousPageUrl() }}" class="page-link">‹</a>
            @endif
            @foreach($sessions->getUrlRange(max(1,$sessions->currentPage()-2), min($sessions->lastPage(),$sessions->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="page-link {{ $page == $sessions->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach
            @if($sessions->hasMorePages())
                <a href="{{ $sessions->nextPageUrl() }}" class="page-link">›</a>
            @else
                <span class="page-link disabled">›</span>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection