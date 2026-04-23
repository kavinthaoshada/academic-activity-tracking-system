@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Academic Activity Reports')

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1>Reports</h1>
        <p>Generate Excel academic activity reports matching the institutional format</p>
    </div>
</div>

{{-- Tab styles --}}
<style>
.report-tabs { display:flex; gap:0; border-bottom:2px solid var(--gray-100); margin-bottom:20px; }
.report-tab  { padding:10px 22px; font-size:.85rem; font-weight:600; cursor:pointer; border:none; background:none;
               color:var(--gray-400); border-bottom:2px solid transparent; margin-bottom:-2px; transition:all .15s; }
.report-tab:hover  { color:var(--teal-dark); }
.report-tab.active { color:var(--teal-dark); border-bottom-color:var(--teal); }
.tab-panel  { display:none; }
.tab-panel.active { display:grid; grid-template-columns:340px 1fr; gap:20px; align-items:start; }
</style>

{{-- Determine active tab from request --}}
@php
    $activeTab = 'weekly';
    if (request('report_type') === 'course')   $activeTab = 'course';
    if (request('report_type') === 'semester') $activeTab = 'semester';
@endphp

{{-- Tabs nav --}}
<div class="report-tabs">
    <button class="report-tab {{ $activeTab === 'weekly'   ? 'active' : '' }}" onclick="switchTab('weekly')">
        Weekly Batch
    </button>
    <button class="report-tab {{ $activeTab === 'course'   ? 'active' : '' }}" onclick="switchTab('course')">
        Course History
    </button>
    <button class="report-tab {{ $activeTab === 'semester' ? 'active' : '' }}" onclick="switchTab('semester')">
        Semester-Wide
    </button>
</div>

{{-- ═══════════════════════════════════════════════
     TAB 1 — Weekly Batch Report
═══════════════════════════════════════════════ --}}
<div id="tab-weekly" class="tab-panel {{ $activeTab === 'weekly' ? 'active' : '' }}">

    {{-- Controls --}}
    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card">
            <div class="card-header" style="background:var(--teal-pale);color:var(--teal-dark)">
                <h3>Weekly Batch Report</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('reports.index') }}" id="previewForm">
                    <input type="hidden" name="report_type" value="weekly">
                    <div class="form-group">
                        <label class="form-label">Batch <span class="required">*</span></label>
                        <select name="batch_id" class="form-control" onchange="this.form.submit()">
                            <option value="">— Select batch —</option>
                            @foreach($batches as $b)
                            <option value="{{ $b->id }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->full_label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Academic Week <span class="required">*</span></label>
                        <select name="week_id" class="form-control" onchange="this.form.submit()"
                            {{ !request('batch_id') ? 'disabled' : '' }}>
                            <option value="">— Select week —</option>
                            @foreach($weeks as $w)
                            <option value="{{ $w->id }}" {{ request('week_id') == $w->id ? 'selected' : '' }}>
                                {{ $w->label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            @if(request('batch_id') && request('week_id') && $activeTab === 'weekly')
            <div class="card-footer" style="flex-direction:column;align-items:stretch;gap:8px">
                <form method="POST" action="{{ route('reports.download-now') }}">
                    @csrf
                    <input type="hidden" name="batch_id" value="{{ request('batch_id') }}">
                    <input type="hidden" name="week_id"  value="{{ request('week_id') }}">
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Download Now (.xlsx)
                    </button>
                </form>
                <!-- <form method="POST" action="{{ route('reports.generate') }}">
                    @csrf
                    <input type="hidden" name="batch_id" value="{{ request('batch_id') }}">
                    <input type="hidden" name="week_id"  value="{{ request('week_id') }}">
                    <button type="submit" class="btn btn-outline" style="width:100%;justify-content:center">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        Email me the report
                    </button>
                </form> -->
            </div>
            @endif
        </div>

        {{-- Column legend --}}
        @include('reports._legend')
    </div>

    {{-- Preview --}}
    @include('reports._preview_panel', ['type' => 'weekly'])
</div>

{{-- ═══════════════════════════════════════════════
     TAB 2 — Course History
═══════════════════════════════════════════════ --}}
<div id="tab-course" class="tab-panel {{ $activeTab === 'course' ? 'active' : '' }}">

    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card">
            <div class="card-header" style="background:var(--teal-pale);color:var(--teal-dark)">
                <h3>Course History Report</h3>
            </div>
            <div class="card-body">
                {{-- GET form → triggers preview --}}
                <form method="GET" action="{{ route('reports.index') }}" id="coursePreviewForm">
                    <input type="hidden" name="report_type" value="course">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Select Course <span class="required">*</span></label>
                        <select name="course_id" class="form-control" onchange="this.form.submit()">
                            <option value="">— Select course —</option>
                            @foreach($allCourses as $c)
                            <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->batch->full_label }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            @if($activeTab === 'course' && request('course_id'))
            <div class="card-footer">
                {{-- Separate POST form for actual download — NOT nested inside the GET form --}}
                <form method="POST" action="{{ route('reports.course') }}" style="width:100%">
                    @csrf
                    <input type="hidden" name="course_id" value="{{ request('course_id') }}">
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Download Full History (.xlsx)
                    </button>
                </form>
            </div>
            @endif
        </div>

        @include('reports._legend')
    </div>

    @include('reports._preview_panel', ['type' => 'course'])
</div>

{{-- ═══════════════════════════════════════════════
     TAB 3 — Semester-Wide
═══════════════════════════════════════════════ --}}
<div id="tab-semester" class="tab-panel {{ $activeTab === 'semester' ? 'active' : '' }}">

    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card">
            <div class="card-header" style="background:var(--teal-pale);color:var(--teal-dark)">
                <h3>Semester-Wide Report</h3>
            </div>
            <div class="card-body">
                {{-- GET form → triggers preview --}}
                <form method="GET" action="{{ route('reports.index') }}" id="semesterPreviewForm">
                    <input type="hidden" name="report_type" value="semester">
                    <div class="form-row form-row-2">
                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label">Semester <span class="required">*</span></label>
                            <select name="semester" class="form-control" onchange="this.form.submit()">
                                <option value="">— Select —</option>
                                @foreach($semesters as $s)
                                <option value="{{ $s }}" {{ request('semester') == $s ? 'selected' : '' }}>
                                    Sem {{ $s }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label">Week No. <span class="required">*</span></label>
                            <input type="number" name="week_number" class="form-control"
                                   placeholder="e.g. 7" min="1"
                                   value="{{ request('week_number') }}"
                                   onchange="this.form.submit()">
                        </div>
                    </div>
                </form>
            </div>

            @if($activeTab === 'semester' && request('semester') && request('week_number'))
            <div class="card-footer">
                {{-- Separate POST form — NOT nested --}}
                <form method="POST" action="{{ route('reports.semester') }}" style="width:100%">
                    @csrf
                    <input type="hidden" name="semester"    value="{{ request('semester') }}">
                    <input type="hidden" name="week_number" value="{{ request('week_number') }}">
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Download Semester Report (.xlsx)
                    </button>
                </form>
            </div>
            @endif
        </div>

        @include('reports._legend')
    </div>

    @include('reports._preview_panel', ['type' => 'semester'])
</div>

{{-- Tab switching JS --}}
<script>
function switchTab(name) {
    document.querySelectorAll('.report-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    event.currentTarget.classList.add('active');
}
</script>
@endsection