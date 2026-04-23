@extends('layouts.app')
@section('title', isset($academicWeek) ? 'Edit Week' : 'Add Week')
@section('page-title', isset($academicWeek) ? 'Edit Academic Week' : 'Add Academic Week')
@section('breadcrumb')
    <a href="{{ route('admin.academic-weeks.index') }}">Academic Weeks</a>
    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    <span>{{ isset($academicWeek) ? 'Edit Week '.$academicWeek->week_number : 'Add Week' }}</span>
@endsection

@section('content')
<div style="max-width:600px">
<form method="POST" action="{{ isset($academicWeek) ? route('admin.academic-weeks.update', $academicWeek) : route('admin.academic-weeks.store') }}">
    @csrf
    @if(isset($academicWeek)) @method('PUT') @endif

    <div class="card">
        <div class="card-header"><h3>{{ isset($academicWeek) ? 'Edit Week '.$academicWeek->week_number : 'New Academic Week' }}</h3></div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/></svg>
                <div class="alert-message">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
            </div>
            @endif

            @if(!isset($academicWeek))
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Batch <span class="required">*</span></label>
                    <select name="batch_id" class="form-control {{ $errors->has('batch_id') ? 'is-invalid' : '' }}" required>
                        <option value="">— Select batch —</option>
                        @foreach($batches as $b)
                        <option value="{{ $b->id }}" {{ old('batch_id') == $b->id ? 'selected' : '' }}>{{ $b->full_label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Week Number <span class="required">*</span></label>
                    <input type="number" name="week_number" value="{{ old('week_number') }}"
                           class="form-control {{ $errors->has('week_number') ? 'is-invalid' : '' }}"
                           min="1" max="52" required placeholder="e.g. 7">
                </div>
            </div>
            @endif

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Start Date <span class="required">*</span></label>
                    <input type="date" name="start_date"
                           value="{{ old('start_date', isset($academicWeek) ? $academicWeek->start_date->format('Y-m-d') : '') }}"
                           class="form-control {{ $errors->has('start_date') ? 'is-invalid' : '' }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">End Date <span class="required">*</span></label>
                    <input type="date" name="end_date"
                           value="{{ old('end_date', isset($academicWeek) ? $academicWeek->end_date->format('Y-m-d') : '') }}"
                           class="form-control {{ $errors->has('end_date') ? 'is-invalid' : '' }}" required>
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Working Days <span class="required">*</span></label>
                    <select name="working_days" id="workingDaysSelect"
                            class="form-control {{ $errors->has('working_days') ? 'is-invalid' : '' }}"
                            onchange="syncWeekType(this.value)" required>
                        @for($d = 0; $d <= 6; $d++)
                        <option value="{{ $d }}" {{ old('working_days', $academicWeek->working_days ?? 6) == $d ? 'selected' : '' }}>
                            {{ $d }} day{{ $d !== 1 ? 's' : '' }}
                        </option>
                        @endfor
                    </select>
                    <div class="form-hint">6 = full week (Mon–Sat), 5 = reduced, &lt;5 = holiday/short</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Week Type <span class="required">*</span></label>
                    <select name="week_type" id="weekTypeSelect"
                            class="form-control {{ $errors->has('week_type') ? 'is-invalid' : '' }}" required>
                        @foreach(['full' => 'Full (6 days)', 'reduced' => 'Reduced (5 days)', 'holiday' => 'Holiday / Short', 'custom' => 'Custom'] as $val => $lbl)
                        <option value="{{ $val }}" {{ old('week_type', $academicWeek->week_type ?? 'full') === $val ? 'selected' : '' }}>
                            {{ $lbl }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Planning preview box --}}
            <div style="background:var(--teal-pale);border:1px solid var(--teal-light);border-radius:var(--radius-md);padding:14px 16px;margin-bottom:20px" id="planningPreview">
                <div style="font-size:.78rem;font-weight:600;color:var(--teal);margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em">Planned sessions for this week type</div>
                <div style="display:flex;gap:20px">
                    <div><span style="font-size:.78rem;color:var(--gray-500)">Theory (45 hrs):</span> <strong id="prevTheory45" style="color:var(--teal-dark)">3</strong></div>
                    <div><span style="font-size:.78rem;color:var(--gray-500)">Theory (30 hrs):</span> <strong id="prevTheory30" style="color:var(--teal-dark)">2</strong></div>
                    <div><span style="font-size:.78rem;color:var(--gray-500)">Practical (30 hrs):</span> <strong id="prevPractical" style="color:var(--teal-dark)">2</strong></div>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2"
                          placeholder="e.g. Diwali holiday — 3 working days">{{ old('notes', $academicWeek->notes ?? '') }}</textarea>
                <div class="form-hint">Optional: explain why this week has fewer days.</div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('admin.academic-weeks.index', isset($academicWeek) ? ['batch_id' => $academicWeek->batch_id] : []) }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">
                {{ isset($academicWeek) ? 'Save Changes' : 'Add Week' }}
            </button>
        </div>
    </div>{{-- /.card --}}

    @if(isset($academicWeek) && $academicWeek->batch->courses->count() > 0)
    <div style="margin-top:28px">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px">
            <h4 style="margin:0; font-size:.95rem; font-weight:600; color:var(--gray-800)">Customize Planned Sessions</h4>
            <span class="badge badge-gold">Optional Overrides</span>
        </div>
        <div class="card" style="box-shadow:none; border:1px solid var(--gray-200);">
            <div style="padding:12px 16px; background:var(--gray-50); border-bottom:1px solid var(--gray-200); border-radius:var(--radius-md) var(--radius-md) 0 0;">
                <p style="margin:0; font-size:.82rem; color:var(--gray-500); line-height:1.5">
                    Leave blank to use auto-calculated defaults. Enter a number to force an exact session count for this week.
                </p>
            </div>
            <table class="table" style="margin:0">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th style="width:160px; text-align:center">Calculated Default</th>
                        <th style="width:160px; text-align:center">Custom Override</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($academicWeek->batch->courses as $c)
                    <tr>
                        <td style="font-weight:500; vertical-align:middle;">{{ $c->name }}</td>
                        <td style="text-align:center; vertical-align:middle;">
                            <span class="badge badge-gray">
                                {{ app(\App\Services\SessionPlanningService::class)->calculatePlannedSessions($c, $academicWeek, true) }} sessions
                            </span>
                        </td>
                        <td style="text-align:center; vertical-align:middle;">
                            <input type="number"
                                   name="planned_session_overrides[{{ $c->id }}]"
                                   value="{{ $academicWeek->planned_session_overrides[$c->id] ?? '' }}"
                                   class="form-control"
                                   style="width:90px; margin:0 auto; text-align:center;"
                                   min="0" max="20"
                                   placeholder="Auto">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px; display:flex; justify-content:flex-end">
            <button type="submit" class="btn btn-secondary" style="font-size:.85rem">
                Save Session Overrides
            </button>
        </div>
    </div>
    @endif

</form>
</div>

@push('scripts')
<script>
// Planning preview based on week type
const previews = {
    full:     { t45: 3,  t30: 2,  p: 2 },
    reduced:  { t45: 1,  t30: 1,  p: 1 },
    holiday:  { t45: 0,  t30: 0,  p: 0 },
    custom:   { t45: '?', t30: '?', p: '?' },
};

function updatePreview(type) {
    const p = previews[type] || previews.custom;
    document.getElementById('prevTheory45').textContent  = p.t45;
    document.getElementById('prevTheory30').textContent  = p.t30;
    document.getElementById('prevPractical').textContent = p.p;
}

function syncWeekType(days) {
    const d = parseInt(days);
    const sel = document.getElementById('weekTypeSelect');
    if (d >= 6) sel.value = 'full';
    else if (d === 5) sel.value = 'reduced';
    else if (d > 0) sel.value = 'holiday';
    else sel.value = 'holiday';
    updatePreview(sel.value);
}

document.getElementById('weekTypeSelect')?.addEventListener('change', function() {
    updatePreview(this.value);
});

// Init preview
updatePreview(document.getElementById('weekTypeSelect')?.value || 'full');
</script>
@endpush
@endsection