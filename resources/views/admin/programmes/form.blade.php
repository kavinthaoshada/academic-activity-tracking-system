@extends('layouts.app')
@section('title', isset($programme) ? 'Edit Programme' : 'New Programme')
@section('page-title', isset($programme) ? 'Edit Programme' : 'New Programme')
@section('breadcrumb')
    <a href="{{ route('admin.programmes.index') }}">Programmes</a>
    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    <span>{{ isset($programme) ? 'Edit' : 'Create' }}</span>
@endsection

@section('content')
<div style="max-width:600px">
<form method="POST" action="{{ isset($programme) ? route('admin.programmes.update', $programme) : route('admin.programmes.store') }}">
    @csrf
    @if(isset($programme)) @method('PUT') @endif

    <div class="card">
        <div class="card-header"><h3>{{ isset($programme) ? 'Edit' : 'New' }} Programme</h3></div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg>
                <div class="alert-message">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
            </div>
            @endif

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Programme Name <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $programme->name ?? '') }}"
                           class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           placeholder="e.g. BCA (Hons.)" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Code <span class="required">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $programme->code ?? '') }}"
                           class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}"
                           placeholder="e.g. BCA" required style="text-transform:uppercase">
                    <div class="form-hint">Unique short code — used in reports.</div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Semester Duration (weeks) <span class="required">*</span></label>
                <input type="number" name="total_weeks" value="{{ old('total_weeks', $programme->total_weeks ?? 15) }}"
                       class="form-control {{ $errors->has('total_weeks') ? 'is-invalid' : '' }}"
                       min="1" max="52" required>
                <div class="form-hint">This is the "15" in the weekly target formula: <strong>Total Hours ÷ Total Weeks = Weekly Target</strong></div>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" placeholder="Optional description…" rows="3">{{ old('description', $programme->description ?? '') }}</textarea>
            </div>

            <div class="form-group" style="margin-bottom:0">
                <label class="toggle-wrap">
                    <div class="toggle">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $programme->is_active ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </div>
                    <span style="font-size:.875rem;font-weight:500;color:var(--gray-700)">Programme is Active</span>
                </label>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('admin.programmes.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">
                {{ isset($programme) ? 'Save Changes' : 'Create Programme' }}
            </button>
        </div>
    </div>
</form>
</div>
@endsection