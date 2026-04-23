@extends('layouts.app')
@section('title', isset($batch) ? 'Edit Batch' : 'New Batch')
@section('page-title', isset($batch) ? 'Edit Batch' : 'New Batch')
@section('breadcrumb')
    <a href="{{ route('admin.batches.index') }}">Batches</a>
    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    <span>{{ isset($batch) ? 'Edit' : 'Create' }}</span>
@endsection

@section('content')
<div style="max-width:680px">
<form method="POST" action="{{ isset($batch) ? route('admin.batches.update', $batch) : route('admin.batches.store') }}">
    @csrf
    @if(isset($batch)) @method('PUT') @endif

    <div class="card">
        <div class="card-header"><h3>{{ isset($batch) ? 'Edit' : 'New' }} Batch</h3></div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg>
                <div class="alert-message">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
            </div>
            @endif

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Programme <span class="required">*</span></label>
                    <select name="programme_id" class="form-control {{ $errors->has('programme_id') ? 'is-invalid' : '' }}" required>
                        <option value="">— Select programme —</option>
                        @foreach($programmes as $prog)
                        <option value="{{ $prog->id }}" {{ old('programme_id', $batch->programme_id ?? '') == $prog->id ? 'selected' : '' }}>
                            {{ $prog->name }} ({{ $prog->code }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Semester <span class="required">*</span></label>
                    <select name="semester" class="form-control {{ $errors->has('semester') ? 'is-invalid' : '' }}" required>
                        @for($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}" {{ old('semester', $batch->semester ?? '') == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Year Range <span class="required">*</span></label>
                    <input type="text" name="year_range" value="{{ old('year_range', $batch->year_range ?? '') }}"
                           class="form-control {{ $errors->has('year_range') ? 'is-invalid' : '' }}"
                           placeholder="e.g. 2024-28" required>
                    <div class="form-hint">Appears in reports and labels.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Division</label>
                    <input type="text" name="division" value="{{ old('division', $batch->division ?? '') }}"
                           class="form-control" placeholder="A, B, or leave blank">
                    <div class="form-hint">Leave blank if batch is not divided.</div>
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Start Date <span class="required">*</span></label>
                    <input type="date" name="start_date" value="{{ old('start_date', isset($batch) ? $batch->start_date->format('Y-m-d') : '') }}"
                           class="form-control {{ $errors->has('start_date') ? 'is-invalid' : '' }}" required>
                    <div class="form-hint">Academic weeks will be auto-generated from this date.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" value="{{ old('end_date', isset($batch) && $batch->end_date ? $batch->end_date->format('Y-m-d') : '') }}"
                           class="form-control">
                </div>
            </div>

            @if(!isset($batch))
            <div class="alert alert-info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span class="alert-message">Academic weeks will be automatically generated based on the programme's semester length. You can customize them afterwards.</span>
            </div>
            @endif

            <div class="form-group" style="margin-bottom:0">
                <label class="toggle-wrap">
                    <div class="toggle">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $batch->is_active ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </div>
                    <span style="font-size:.875rem;font-weight:500;color:var(--gray-700)">Batch is Active</span>
                </label>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('admin.batches.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">
                {{ isset($batch) ? 'Save Changes' : 'Create Batch' }}
            </button>
        </div>
    </div>
</form>
</div>
@endsection