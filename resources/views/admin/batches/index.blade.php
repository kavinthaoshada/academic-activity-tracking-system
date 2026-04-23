@extends('layouts.app')
@section('title', 'Batches')
@section('page-title', 'Batches')

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1>Batches</h1>
        <p>Manage student batches across programmes and semesters</p>
    </div>
    <a href="{{ route('admin.batches.create') }}" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Batch
    </a>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Batch</th>
                    <th>Programme</th>
                    <th>Semester</th>
                    <th>Division</th>
                    <th>Start Date</th>
                    <th>Weeks</th>
                    <th>Courses</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $b)
                <tr>
                    <td>
                        <div style="font-weight:600;color:var(--teal-dark)">{{ $b->year_range }}</div>
                    </td>
                    <td>
                        <span style="font-size:.82rem;font-weight:500;color:var(--gray-700)">{{ $b->programme->name }}</span>
                        <div style="font-size:.72rem;color:var(--gray-400)">{{ $b->programme->code }}</div>
                    </td>
                    <td>
                        <span class="badge badge-teal">Sem {{ $b->semester }}</span>
                    </td>
                    <td style="font-size:.84rem;color:var(--gray-500)">{{ $b->division ?? '—' }}</td>
                    <td style="font-size:.82rem;color:var(--gray-500)">{{ $b->start_date->format('d M Y') }}</td>
                    <td>
                        <span style="font-size:.875rem;font-weight:600;color:var(--teal)">
                            {{ $b->academicWeeks()->count() }} / {{ $b->programme->total_weeks }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size:.875rem;font-weight:600;color:var(--teal)">{{ $b->courses_count }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $b->is_active ? 'badge-success' : 'badge-gray' }}">
                            {{ $b->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="actions">
                        <a href="{{ route('admin.batches.show', $b) }}" class="btn btn-ghost btn-sm" title="View">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        <a href="{{ route('admin.batches.edit', $b) }}" class="btn btn-ghost btn-sm" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.batches.destroy', $b) }}" style="display:inline"
                              onsubmit="return confirm('Delete this batch? This will also delete all academic weeks.')">
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
                        <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
                        <h3>No batches yet</h3>
                        <p>Create your first batch to start tracking sessions.</p>
                        <a href="{{ route('admin.batches.create') }}" class="btn btn-primary">Create Batch</a>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($batches->hasPages())
    <div class="card-footer">
        <span style="font-size:.82rem;color:var(--gray-400)">{{ $batches->total() }} batches</span>
        <div class="pagination">
            @if(!$batches->onFirstPage())<a href="{{ $batches->previousPageUrl() }}" class="page-link">‹</a>@endif
            @foreach($batches->getUrlRange(max(1,$batches->currentPage()-2),min($batches->lastPage(),$batches->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="page-link {{ $page == $batches->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach
            @if($batches->hasMorePages())<a href="{{ $batches->nextPageUrl() }}" class="page-link">›</a>@endif
        </div>
    </div>
    @endif
</div>
@endsection