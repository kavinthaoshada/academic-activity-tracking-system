@extends('layouts.app')
@section('title', 'Log Sessions')
@section('page-title', 'Log Sessions')
@section('breadcrumb')
    <a href="{{ route('sessions.index') }}">Sessions</a>
    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    <span>New Entry</span>
@endsection

@section('content')

{{-- Step 1: Select batch & week --}}
<form method="GET" action="{{ route('sessions.create') }}" id="filterForm">
<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <h3>Select Batch &amp; Week</h3>
        <span class="badge badge-teal">Step 1 of 2</span>
    </div>
    <div class="card-body">
        <div class="form-row form-row-2">
            <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Batch <span class="required">*</span></label>
                <select name="batch_id" class="form-control" onchange="this.form.elements['week_id'].value=''; this.form.submit()" id="batchSelect">
                    <option value="">— Select a batch —</option>
                    @foreach($batches as $batch)
                    <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>
                        {{ $batch->full_label }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Academic Week <span class="required">*</span></label>
                <select name="week_id" class="form-control" onchange="this.form.submit()" {{ !request('batch_id') ? 'disabled' : '' }}>
                    <option value="">— Select a week —</option>
                    @if(isset($weeks))
                     @foreach($weeks as $listWeek)
                    <option value="{{ $listWeek->id }}" {{ request('week_id') == $listWeek->id ? 'selected' : '' }}>
                        {{ $listWeek->label }} — {{ ucfirst($listWeek->week_type) }} ({{ $listWeek->working_days }} days)
                    </option>
                    @endforeach
                    @endif
                </select>
            </div>
        </div>
    </div>
</div>
</form>

{{-- Step 2: Enter sessions (only shown when batch + week selected) --}}
@if(isset($courses) && $courses->count() > 0 && isset($week))

{{-- Week info banner --}}
<div style="background:var(--teal-pale);border:1px solid var(--teal-light);border-radius:var(--radius-md);padding:14px 20px;display:flex;align-items:center;gap:24px;margin-bottom:20px;flex-wrap:wrap">
    <div style="display:flex;align-items:center;gap:9px">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--teal)" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <span style="font-size:.84rem;font-weight:600;color:var(--teal-dark)">{{ $week->label }}</span>
    </div>
    <span class="week-chip {{ $week->week_type }}">{{ ucfirst($week->week_type) }} week</span>
    <span style="font-size:.82rem;color:var(--teal)">{{ $week->working_days }} working days</span>
    <span style="font-size:.82rem;color:var(--teal)">{{ $week->start_date->format('d M') }} – {{ $week->end_date->format('d M, Y') }}</span>
    @if($week->notes)
    <span style="font-size:.8rem;color:var(--gray-500);font-style:italic">{{ $week->notes }}</span>
    @endif
</div>

<form method="POST" action="{{ route('sessions.store') }}">
    @csrf
    <input type="hidden" name="academic_week_id" value="{{ $week->id }}">

    <div class="card">
        <div class="card-header">
            <h3>Enter Session Data</h3>
            <span class="badge badge-teal">Step 2 of 2</span>
        </div>

        <div class="table-wrap">
            <table class="table" style="min-width:900px">
                <thead>
                    <tr>
                        <th style="width:36px">#</th>
                        <th>Course</th>
                        <th style="width:140px">Faculty</th>
                        <th style="width:80px">Type</th>
                        <th style="width:80px">Total Hrs</th>
                        <th style="width:90px">Weekly<br>Target</th>
                        <th style="width:110px">
                            <span style="color:var(--teal)">Planned</span>
                            <div class="form-hint" style="font-weight:400;text-transform:none;letter-spacing:0;margin:0">for this week</div>
                        </th>
                        <th style="width:110px">
                            <span style="color:var(--gold-dark)">Actual</span>
                            <div class="form-hint" style="font-weight:400;text-transform:none;letter-spacing:0;margin:0">conducted</div>
                        </th>
                        <th style="width:80px">Variance</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($courses as $i => $course)
                    @php
                        $existing  = $course->weeklySessions->where('academic_week_id', $week->id)->first();
                        $planned   = $existing?->planned_sessions ?? ($plannedDefaults[$course->id] ?? 0);
                        $actual    = $existing?->actual_sessions  ?? 0;
                        $faculty   = $course->assignments->first()?->user?->name ?? '—';
                    @endphp
                    <tr>
                        <td style="color:var(--gray-400);font-size:.8rem">{{ $i + 1 }}</td>
                        <td>
                            <div style="font-weight:500;color:var(--gray-800)">{{ $course->name }}</div>
                            <div style="font-size:.72rem;color:var(--gray-400)">{{ $course->code }}</div>
                        </td>
                        <td style="font-size:.82rem;color:var(--gray-600)">{{ $faculty }}</td>
                        <td>
                            <span class="badge {{ $course->type === 'theory' ? 'badge-teal' : 'badge-gold' }}">
                                {{ ucfirst($course->type) }}
                            </span>
                        </td>
                        <td style="font-size:.875rem;color:var(--gray-600)">{{ $course->total_hours }}</td>
                        <td>
                            <span style="font-size:.875rem;font-weight:600;color:var(--teal)">
                                {{ $course->weekly_target }}
                            </span>
                        </td>
                        <td>
                            <input type="number"
                                   name="sessions[{{ $course->id }}][planned]"
                                   value="{{ old("sessions.{$course->id}.planned", $planned) }}"
                                   class="form-control session-planned"
                                   data-row="{{ $course->id }}"
                                   min="0" max="20" style="padding:7px 10px">
                        </td>
                        <td>
                            <input type="number"
                                   name="sessions[{{ $course->id }}][actual]"
                                   value="{{ old("sessions.{$course->id}.actual", $actual) }}"
                                   class="form-control session-actual"
                                   data-row="{{ $course->id }}"
                                   min="0" max="20" style="padding:7px 10px">
                        </td>
                        <td>
                            <span class="variance-display" id="var-{{ $course->id }}"
                                  data-planned="{{ $planned }}" data-actual="{{ $actual }}">
                                @php $v = $actual - $planned; @endphp
                                <span class="{{ $v > 0 ? 'variance-pos' : ($v < 0 ? 'variance-neg' : 'variance-zero') }}">
                                    {{ $v > 0 ? '+' : '' }}{{ $v }}
                                </span>
                            </span>
                        </td>
                        <td>
                            <input type="text"
                                   name="sessions[{{ $course->id }}][remarks]"
                                   value="{{ old("sessions.{$course->id}.remarks", $existing?->remarks) }}"
                                   class="form-control"
                                   placeholder="Optional note…"
                                   style="padding:7px 10px;font-size:.8rem">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            <a href="{{ route('sessions.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
                Save All Sessions
            </button>
        </div>
    </div>
</form>

@elseif(request('batch_id') && request('week_id'))
<div class="card">
    <div class="empty-state">
        <div class="empty-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
        </div>
        <h3>No courses assigned</h3>
        <p>There are no courses in this batch assigned to you.</p>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
// Live variance calculation
function updateVariance(courseId) {
    const planned = parseInt(document.querySelector(`input.session-planned[data-row="${courseId}"]`)?.value) || 0;
    const actual  = parseInt(document.querySelector(`input.session-actual[data-row="${courseId}"]`)?.value) || 0;
    const v = actual - planned;
    const el = document.getElementById(`var-${courseId}`);
    if (el) {
        const cls = v > 0 ? 'variance-pos' : (v < 0 ? 'variance-neg' : 'variance-zero');
        el.innerHTML = `<span class="${cls}">${v > 0 ? '+' : ''}${v}</span>`;
    }
}

document.querySelectorAll('.session-planned, .session-actual').forEach(inp => {
    inp.addEventListener('input', () => updateVariance(inp.dataset.row));
});
</script>
@endpush