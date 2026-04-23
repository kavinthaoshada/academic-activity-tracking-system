@extends('layouts.app')
@section('title', 'Courses')
@section('page-title', 'Courses')

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1>Courses</h1>
        <p>All courses across batches with faculty assignments</p>
    </div>
    <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Course
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
    <select name="type" class="form-control" onchange="this.form.submit()">
        <option value="">All Types</option>
        <option value="theory"    {{ request('type') === 'theory'    ? 'selected' : '' }}>Theory</option>
        <option value="practical" {{ request('type') === 'practical' ? 'selected' : '' }}>Practical</option>
    </select>
    <a href="{{ route('admin.courses.index') }}" class="btn btn-ghost">Clear</a>
</form>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Batch</th>
                    <th>Type</th>
                    <th>Total Hours</th>
                    <th>Weekly Target</th>
                    <th>Credit Hrs</th>
                    <th>Faculty</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $course)
                <tr>
                    <td>
                        <div style="font-weight:500;color:var(--gray-800)">{{ $course->name }}</div>
                        @if($course->code)
                        <div style="font-size:.72rem;color:var(--gray-400);margin-top:2px">{{ $course->code }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="font-size:.82rem;color:var(--gray-600)">{{ $course->batch->programme->code }}</div>
                        <div style="font-size:.72rem;color:var(--gray-400)">{{ $course->batch->full_label }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $course->type === 'theory' ? 'badge-teal' : 'badge-gold' }}">
                            {{ ucfirst($course->type) }}
                        </span>
                    </td>
                    <td>
                        <span style="font-weight:600;color:var(--gray-700)">{{ $course->total_hours }}</span>
                        <span style="font-size:.72rem;color:var(--gray-400)">hrs</span>
                    </td>
                    <td>
                        <span style="font-weight:600;color:var(--teal)">{{ $course->weekly_target }}</span>
                        <span style="font-size:.72rem;color:var(--gray-400)">/wk</span>
                    </td>
                    <td style="color:var(--gray-500)">{{ $course->credit_hours ?? '—' }}</td>
                    <td>
                        @foreach($course->assignments as $assignment)
                        <div style="font-size:.78rem;color:var(--gray-600)">{{ $assignment->user->name }}</div>
                        @endforeach
                        @if($course->assignments->isEmpty())
                        <span style="font-size:.78rem;color:var(--danger)">Unassigned</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $course->is_active ? 'badge-success' : 'badge-gray' }}">
                            {{ $course->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="actions">
                        <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-ghost btn-sm" title="View">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-ghost btn-sm" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" style="display:inline"
                              onsubmit="return confirm('Delete this course?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-ghost btn-sm">
                                <svg viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" width="14" height="14"><polyline points="3,6 5,6 21,6"/><path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6m3,0V4a1,1,0,0,1,1-1h4a1,1,0,0,1,1,1v2"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9">
                    <div class="empty-state">
                        <div class="empty-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        </div>
                        <h3>No courses found</h3>
                        <p>Add courses to a batch to start tracking sessions.</p>
                        <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">Add First Course</a>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($courses->hasPages())
    <div class="card-footer">
        <span style="font-size:.82rem;color:var(--gray-400)">{{ $courses->total() }} courses</span>
        <div class="pagination">
            @if(!$courses->onFirstPage())<a href="{{ $courses->previousPageUrl() }}" class="page-link">‹</a>@endif
            @foreach($courses->getUrlRange(max(1,$courses->currentPage()-2),min($courses->lastPage(),$courses->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="page-link {{ $page == $courses->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach
            @if($courses->hasMorePages())<a href="{{ $courses->nextPageUrl() }}" class="page-link">›</a>@endif
        </div>
    </div>
    @endif
</div>
@endsection