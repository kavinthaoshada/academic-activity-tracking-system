@extends('layouts.app')
@section('title', isset($course) ? 'Edit Course' : 'New Course')
@section('page-title', isset($course) ? 'Edit Course' : 'New Course')
@section('breadcrumb')
    <a href="{{ route('admin.courses.index') }}">Courses</a>
    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    <span>{{ isset($course) ? 'Edit' : 'Create' }}</span>
@endsection

@section('content')
<div style="max-width:740px">
<form method="POST" action="{{ isset($course) ? route('admin.courses.update', $course) : route('admin.courses.store') }}">
    @csrf
    @if(isset($course)) @method('PUT') @endif

    <div class="card" style="margin-bottom:16px">
        <div class="card-header"><h3>Course Details</h3></div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/></svg>
                <div class="alert-message">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
            </div>
            @endif

            <div class="form-group">
                <label class="form-label">Batch <span class="required">*</span></label>
                <select name="batch_id" class="form-control {{ $errors->has('batch_id') ? 'is-invalid' : '' }}" required>
                    <option value="">— Select batch —</option>
                    @foreach($batches as $b)
                    <option value="{{ $b->id }}" {{ old('batch_id', $course->batch_id ?? request('batch_id')) == $b->id ? 'selected' : '' }}>
                        {{ $b->full_label }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Course Name <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $course->name ?? '') }}"
                           class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           placeholder="e.g. Java Programming (BCA)" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Course Code</label>
                    <input type="text" name="code" value="{{ old('code', $course->code ?? '') }}"
                           class="form-control" placeholder="e.g. OOP-JAVA">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Type <span class="required">*</span></label>
                <div style="display:flex;gap:16px;margin-top:4px">
                    @foreach(['theory' => 'Theory', 'practical' => 'Practical'] as $val => $lbl)
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px 18px;border:1.5px solid var(--gray-200);border-radius:var(--radius-md);transition:var(--transition);{{ old('type', $course->type ?? '') === $val ? 'border-color:var(--teal);background:var(--teal-pale)' : '' }}" id="type-lbl-{{ $val }}">
                        <input type="radio" name="type" value="{{ $val }}"
                               {{ old('type', $course->type ?? '') === $val ? 'checked' : '' }}
                               style="accent-color:var(--teal)"
                               onchange="document.querySelectorAll('[id^=type-lbl-]').forEach(l=>{l.style.borderColor='var(--gray-200)';l.style.background=''});this.closest('label').style.borderColor='var(--teal)';this.closest('label').style.background='var(--teal-pale)'">
                        <span style="font-size:.875rem;font-weight:500;color:var(--gray-700)">{{ $lbl }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Total Hours <span class="required">*</span></label>
                    <input type="number" name="total_hours"
                           value="{{ old('total_hours', $course->total_hours ?? '') }}"
                           class="form-control {{ $errors->has('total_hours') ? 'is-invalid' : '' }}"
                           placeholder="e.g. 45 or 30" min="1" required
                           id="totalHoursInput">
                    <div class="form-hint">
                        Weekly target = Total Hours ÷ Semester Weeks.
                        <span id="weeklyTargetPreview" style="font-weight:600;color:var(--teal)"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Credit Hours (Cumulative Target)</label>
                    <input type="number" name="credit_hours"
                           value="{{ old('credit_hours', $course->credit_hours ?? '') }}"
                           class="form-control" placeholder="e.g. 45 or 30" min="0">
                    <div class="form-hint">Used as the cumulative target column in the report.</div>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:0">
                <label class="toggle-wrap">
                    <div class="toggle">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $course->is_active ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </div>
                    <span style="font-size:.875rem;font-weight:500;color:var(--gray-700)">Course is Active</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Faculty Assignment --}}
    <div class="card" style="margin-bottom:16px">
        <div class="card-header">
            <h3>Faculty Assignment</h3>
            <span style="font-size:.78rem;color:var(--gray-400)">Assign one or more faculty members</span>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px">
                @foreach($faculty as $f)
                @php $checked = in_array($f->id, old('faculty_ids', $assignedFacultyIds ?? [])); @endphp
                <label style="display:flex;align-items:center;gap:10px;padding:10px 14px;border:1.5px solid {{ $checked ? 'var(--teal)' : 'var(--gray-200)' }};border-radius:var(--radius-md);cursor:pointer;transition:var(--transition);background:{{ $checked ? 'var(--teal-pale)' : 'transparent' }}" id="faculty-lbl-{{ $f->id }}">
                    <input type="checkbox" name="faculty_ids[]" value="{{ $f->id }}"
                           {{ $checked ? 'checked' : '' }}
                           style="accent-color:var(--teal);width:16px;height:16px;flex-shrink:0"
                           onchange="const l=document.getElementById('faculty-lbl-{{ $f->id }}');l.style.borderColor=this.checked?'var(--teal)':'var(--gray-200)';l.style.background=this.checked?'var(--teal-pale)':''">
                    <div style="min-width:0">
                        <div class="avatar" style="width:26px;height:26px;font-size:.6rem;display:inline-flex;vertical-align:middle;margin-right:6px">{{ $f->initials() }}</div>
                        <span style="font-size:.84rem;font-weight:500;color:var(--gray-800)">{{ $f->name }}</span>
                        @if($f->department)
                        <div style="font-size:.72rem;color:var(--gray-400);margin-top:2px">{{ $f->department }}</div>
                        @endif
                    </div>
                </label>
                @endforeach
            </div>
            @if($faculty->isEmpty())
            <p style="color:var(--gray-400);font-size:.875rem">No staff members available. <a href="{{ route('admin.invitations.index') }}">Invite staff members first.</a></p>
            @endif
        </div>
    </div>

    <div style="display:flex;justify-content:space-between">
        <a href="{{ route('admin.courses.index') }}" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
            {{ isset($course) ? 'Save Changes' : 'Create Course' }}
        </button>
    </div>
</form>
</div>

@push('scripts')
<script>
// Live weekly target preview
const batchSelect   = document.querySelector('select[name="batch_id"]');
const hoursInput    = document.getElementById('totalHoursInput');
const targetPreview = document.getElementById('weeklyTargetPreview');

// Batch -> total_weeks mapping (from PHP)
const batchWeeks = @json($batches->mapWithKeys(fn($b) => [$b->id => $b->programme->total_weeks]));

function updatePreview() {
    const batchId = batchSelect?.value;
    const hours   = parseFloat(hoursInput?.value);
    if (batchId && hours && batchWeeks[batchId]) {
        const target = (hours / batchWeeks[batchId]).toFixed(2);
        targetPreview.textContent = `→ Weekly target: ${target} sessions/week`;
    } else {
        targetPreview.textContent = '';
    }
}

batchSelect?.addEventListener('change', updatePreview);
hoursInput?.addEventListener('input', updatePreview);
updatePreview();
</script>
@endpush
@endsection