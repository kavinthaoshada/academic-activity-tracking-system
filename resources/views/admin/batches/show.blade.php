@extends('layouts.app')
@section('title', $batch->full_label)
@section('page-title', 'Batch Detail')
@section('breadcrumb')
    <a href="{{ route('admin.batches.index') }}">Batches</a>
    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    <span>{{ $batch->full_label }}</span>
@endsection

@section('content')

{{-- Header card --}}
<div class="card" style="margin-bottom:20px">
    <div style="padding:22px 26px;display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px">
        <div style="display:flex;align-items:center;gap:18px">
            <div style="width:56px;height:56px;border-radius:var(--radius-lg);background:var(--teal);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <h1 style="font-size:1.4rem;margin-bottom:4px">{{ $batch->full_label }}</h1>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                    <span class="badge badge-teal">{{ $batch->programme->name }}</span>
                    <span class="badge {{ $batch->is_active ? 'badge-success' : 'badge-gray' }}">{{ $batch->is_active ? 'Active' : 'Inactive' }}</span>
                    <span style="font-size:.78rem;color:var(--gray-400)">{{ $batch->start_date->format('d M Y') }} @if($batch->end_date)— {{ $batch->end_date->format('d M Y') }}@endif</span>
                </div>
            </div>
        </div>
        <div style="display:flex;gap:8px">
            <a href="{{ route('admin.academic-weeks.index', ['batch_id' => $batch->id]) }}" class="btn btn-outline">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Manage Weeks
            </a>
            <a href="{{ route('admin.batches.edit', $batch) }}" class="btn btn-teal">Edit Batch</a>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);border-top:1px solid var(--gray-100)">
        @foreach([
            ['Semester',       "Sem {$batch->semester}"],
            ['Division',       $batch->division ?? 'Undivided'],
            ['Total Weeks',    $batch->programme->total_weeks],
            ['Courses',        $batch->courses->count()],
        ] as [$lbl, $val])
        <div style="padding:16px 22px;{{ !$loop->last ? 'border-right:1px solid var(--gray-100)' : '' }}">
            <div style="font-size:.7rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--gray-400);margin-bottom:5px">{{ $lbl }}</div>
            <div style="font-family:var(--font-display);font-size:1.3rem;color:var(--teal-dark)">{{ $val }}</div>
        </div>
        @endforeach
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">

    {{-- Courses in this batch --}}
    <div class="card">
        <div class="card-header">
            <h3>Courses</h3>
            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary btn-sm">+ Add Course</a>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Type</th>
                        <th>Total Hrs</th>
                        <th>Weekly Target</th>
                        <th>Faculty</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batch->courses as $course)
                    <tr>
                        <td>
                            <div style="font-weight:500;color:var(--gray-800)">{{ $course->name }}</div>
                            @if($course->code)<div style="font-size:.72rem;color:var(--gray-400)">{{ $course->code }}</div>@endif
                        </td>
                        <td>
                            <span class="badge {{ $course->type === 'theory' ? 'badge-teal' : 'badge-gold' }}">
                                {{ ucfirst($course->type) }}
                            </span>
                        </td>
                        <td>{{ $course->total_hours }}</td>
                        <td>
                            <span style="font-weight:600;color:var(--teal)">{{ $course->weekly_target }}/wk</span>
                        </td>
                        <td style="font-size:.82rem;color:var(--gray-500)">
                            {{ $course->assignments->map(fn($a) => $a->user->name)->join(', ') ?: '—' }}
                        </td>
                        <td class="actions">
                            <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-ghost btn-sm">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;padding:28px;color:var(--gray-400)">
                        No courses yet. <a href="{{ route('admin.courses.create') }}">Add the first course →</a>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Academic weeks summary --}}
    <div class="card">
        <div class="card-header">
            <h3>Academic Weeks</h3>
            <a href="{{ route('admin.academic-weeks.index', ['batch_id' => $batch->id]) }}" class="btn btn-ghost btn-sm">View all</a>
        </div>
        <div style="max-height:420px;overflow-y:auto">
            @forelse($batch->academicWeeks as $week)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 18px;border-bottom:1px solid var(--gray-50);gap:8px">
                <div style="display:flex;align-items:center;gap:9px">
                    <div style="width:28px;height:28px;border-radius:50%;background:var(--teal-pale);display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;color:var(--teal);flex-shrink:0">
                        {{ $week->week_number }}
                    </div>
                    <div>
                        <div style="font-size:.8rem;color:var(--gray-700)">{{ $week->start_date->format('d M') }} – {{ $week->end_date->format('d M') }}</div>
                        <div style="font-size:.7rem;color:var(--gray-400)">{{ $week->working_days }} days</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px">
                    <span class="week-chip {{ $week->week_type }}">{{ $week->week_type }}</span>
                    @if($week->is_locked)
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--gray-400)" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    @endif
                </div>
            </div>
            @empty
            <div style="padding:24px;text-align:center;color:var(--gray-400);font-size:.84rem">
                Weeks not generated yet.
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection