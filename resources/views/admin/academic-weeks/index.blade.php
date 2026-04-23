@extends('layouts.app')
@section('title', 'Academic Weeks')
@section('page-title', 'Academic Weeks')

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1>Academic Weeks</h1>
        <p>Configure week types, working days and lock completed weeks</p>
    </div>
    <a href="{{ route('admin.academic-weeks.create') }}" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Week
    </a>
</div>

{{-- Batch filter --}}
<form method="GET" class="filter-bar">
    <select name="batch_id" class="form-control" onchange="this.form.submit()" style="max-width:300px">
        <option value="">— Filter by batch —</option>
        @foreach($batches as $b)
        <option value="{{ $b->id }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>{{ $b->full_label }}</option>
        @endforeach
    </select>
    @if(request('batch_id'))<a href="{{ route('admin.academic-weeks.index') }}" class="btn btn-ghost">Clear</a>@endif
</form>

{{-- Week type legend --}}
<div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;flex-wrap:wrap">
    <span style="font-size:.72rem;color:var(--gray-400);font-weight:600;text-transform:uppercase;letter-spacing:.08em">Week types:</span>
    <span class="week-chip full">Full (6 days) — theory: ×, practical: ×</span>
    <span class="week-chip reduced">Reduced (5 days) — 1 each</span>
    <span class="week-chip holiday">Holiday (&lt;5 days) — 0</span>
    <span class="week-chip custom">Custom — admin defined</span>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:60px">Week #</th>
                    <th>Batch</th>
                    <th>Dates</th>
                    <th style="width:110px">Working Days</th>
                    <th style="width:120px">Type</th>
                    <th>Notes</th>
                    <th style="width:80px">Sessions</th>
                    <th style="width:80px">Lock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($weeks as $week)
                <tr style="{{ $week->is_locked ? 'background:var(--gray-50)' : '' }}">
                    <td>
                        <div style="width:34px;height:34px;border-radius:50%;background:{{ $week->is_locked ? 'var(--gray-200)' : 'var(--teal)' }};display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;color:{{ $week->is_locked ? 'var(--gray-500)' : 'white' }}">
                            {{ $week->week_number }}
                        </div>
                    </td>
                    <td>
                        <div style="font-size:.82rem;font-weight:500;color:var(--gray-700)">{{ $week->batch->full_label }}</div>
                        <div style="font-size:.72rem;color:var(--gray-400)">{{ $week->batch->programme->code }}</div>
                    </td>
                    <td>
                        <div style="font-size:.84rem;color:var(--gray-700)">{{ $week->start_date->format('d M Y') }}</div>
                        <div style="font-size:.78rem;color:var(--gray-400)">to {{ $week->end_date->format('d M Y') }}</div>
                    </td>
                    <td>
                        @if(!$week->is_locked)
                        {{-- Inline working days editor --}}
                        <form method="POST" action="{{ route('admin.academic-weeks.update', $week) }}" style="display:flex;align-items:center;gap:6px">
                            @csrf @method('PUT')
                            <input type="hidden" name="start_date"   value="{{ $week->start_date->format('Y-m-d') }}">
                            <input type="hidden" name="end_date"     value="{{ $week->end_date->format('Y-m-d') }}">
                            <input type="hidden" name="notes"        value="{{ $week->notes }}">
                            <input type="number" name="working_days" value="{{ $week->working_days }}"
                                   min="0" max="6" style="width:52px;padding:5px 7px;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);font-size:.84rem;font-family:var(--font-body);text-align:center"
                                   onchange="
                                       const v = parseInt(this.value);
                                       const sel = this.closest('form').querySelector('[name=week_type]');
                                       sel.value = v >= 6 ? 'full' : (v === 5 ? 'reduced' : (v > 0 ? 'holiday' : 'holiday'));
                                   ">
                            <select name="week_type" style="display:none">
                                @foreach(['full','reduced','holiday','custom'] as $wt)
                                <option value="{{ $wt }}" {{ $week->week_type === $wt ? 'selected' : '' }}>{{ $wt }}</option>
                                @endforeach
                            </select>
                            <button type="submit" title="Save" style="background:var(--teal-pale);border:none;border-radius:var(--radius-sm);padding:5px 8px;cursor:pointer;color:var(--teal)">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
                            </button>
                        </form>
                        @else
                        <span style="font-size:.875rem;font-weight:600;color:var(--gray-500)">{{ $week->working_days }} days</span>
                        @endif
                    </td>
                    <td>
                        <span class="week-chip {{ $week->week_type }}">{{ ucfirst($week->week_type) }}</span>
                    </td>
                    <td>
                        <span style="font-size:.78rem;color:var(--gray-400);font-style:{{ $week->notes ? 'normal' : 'italic' }}">
                            {{ $week->notes ?: 'No notes' }}
                        </span>
                    </td>
                    <td style="text-align:center">
                        <span style="font-size:.875rem;font-weight:600;color:{{ $week->weeklySessions->count() > 0 ? 'var(--success)' : 'var(--gray-300)' }}">
                            {{ $week->weeklySessions->count() }}
                        </span>
                    </td>
                    <td style="text-align:center">
                        <form method="POST"
                              action="{{ $week->is_locked ? route('admin.academic-weeks.unlock', $week) : route('admin.academic-weeks.lock', $week) }}">
                            @csrf
                            <button type="submit"
                                    title="{{ $week->is_locked ? 'Unlock week' : 'Lock week' }}"
                                    style="background:none;border:none;cursor:pointer;padding:4px;color:{{ $week->is_locked ? 'var(--warning)' : 'var(--gray-300)' }};transition:color 0.15s"
                                    onmouseover="this.style.color='{{ $week->is_locked ? 'var(--success)' : 'var(--warning)' }}'"
                                    onmouseout="this.style.color='{{ $week->is_locked ? 'var(--warning)' : 'var(--gray-300)' }}'">
                                @if($week->is_locked)
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                @else
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></svg>
                                @endif
                            </button>
                        </form>
                    </td>
                    <td class="actions">
                        @if(!$week->is_locked)
                        <a href="{{ route('admin.academic-weeks.edit', $week) }}" class="btn btn-ghost btn-sm" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        @endif
                        @if($week->weeklySessions->count() === 0)
                        <form method="POST" action="{{ route('admin.academic-weeks.destroy', $week) }}" style="display:inline"
                              onsubmit="return confirm('Delete week {{ $week->week_number }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-ghost btn-sm">
                                <svg viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" width="14" height="14"><polyline points="3,6 5,6 21,6"/><path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6"/></svg>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="9">
                    <div class="empty-state">
                        <div class="empty-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                        <h3>No academic weeks found</h3>
                        <p>Weeks are auto-generated when you create a batch. Select a batch above or create a new one.</p>
                        <a href="{{ route('admin.batches.create') }}" class="btn btn-primary">Create a Batch</a>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($weeks->hasPages())
    <div class="card-footer">
        <span style="font-size:.82rem;color:var(--gray-400)">{{ $weeks->total() }} weeks total</span>
        <div class="pagination">
            @if(!$weeks->onFirstPage())<a href="{{ $weeks->previousPageUrl() . (request('batch_id') ? '&batch_id='.request('batch_id') : '') }}" class="page-link">‹</a>@endif
            @foreach($weeks->getUrlRange(max(1,$weeks->currentPage()-2),min($weeks->lastPage(),$weeks->currentPage()+2)) as $page => $url)
                <a href="{{ $url . (request('batch_id') ? '&batch_id='.request('batch_id') : '') }}" class="page-link {{ $page == $weeks->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach
            @if($weeks->hasMorePages())<a href="{{ $weeks->nextPageUrl() . (request('batch_id') ? '&batch_id='.request('batch_id') : '') }}" class="page-link">›</a>@endif
        </div>
    </div>
    @endif
</div>
@endsection