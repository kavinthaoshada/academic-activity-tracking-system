@extends('layouts.app')
@section('title', 'Programmes')
@section('page-title', 'Programmes')

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1>Programmes</h1>
        <p>Manage academic programmes offered by the school</p>
    </div>
    <a href="{{ route('admin.programmes.create') }}" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Programme
    </a>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px">
    @forelse($programmes as $prog)
    <div class="card" style="transition:var(--transition)" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
        <div style="padding:20px 22px;border-bottom:1px solid var(--gray-50)">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px">
                <div>
                    <div style="display:inline-block;background:var(--teal);color:white;font-size:.7rem;font-weight:700;letter-spacing:.08em;padding:3px 9px;border-radius:4px;margin-bottom:9px">
                        {{ $prog->code }}
                    </div>
                    <h3 style="font-size:1rem;line-height:1.3">{{ $prog->name }}</h3>
                    @if($prog->description)
                    <p style="font-size:.78rem;color:var(--gray-400);margin-top:5px;line-height:1.5">{{ Str::limit($prog->description, 80) }}</p>
                    @endif
                </div>
                <span class="badge {{ $prog->is_active ? 'badge-success' : 'badge-gray' }}">
                    {{ $prog->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
        <div style="padding:14px 22px;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;gap:16px">
                <div style="text-align:center">
                    <div style="font-family:var(--font-display);font-size:1.2rem;color:var(--teal-dark)">{{ $prog->total_weeks }}</div>
                    <div style="font-size:.68rem;color:var(--gray-400);text-transform:uppercase;letter-spacing:.05em">Weeks</div>
                </div>
                <div style="text-align:center">
                    <div style="font-family:var(--font-display);font-size:1.2rem;color:var(--teal-dark)">{{ $prog->batches_count }}</div>
                    <div style="font-size:.68rem;color:var(--gray-400);text-transform:uppercase;letter-spacing:.05em">Batches</div>
                </div>
            </div>
            <div style="display:flex;gap:6px">
                <a href="{{ route('admin.programmes.edit', $prog) }}" class="btn btn-ghost btn-sm">Edit</a>
                <form method="POST" action="{{ route('admin.programmes.destroy', $prog) }}"
                      onsubmit="return confirm('Delete this programme?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="card" style="grid-column:1/-1">
        <div class="empty-state">
            <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></div>
            <h3>No programmes yet</h3>
            <p>Create your first academic programme to get started.</p>
            <a href="{{ route('admin.programmes.create') }}" class="btn btn-primary">Add Programme</a>
        </div>
    </div>
    @endforelse
</div>
@endsection