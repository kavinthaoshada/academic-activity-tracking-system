@extends('layouts.app')
@section('title', 'Staff Members')
@section('page-title', 'Staff Members')

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1>Staff Members</h1>
        <p>Manage user accounts and access levels</p>
    </div>
    <a href="{{ route('admin.invitations.index') }}" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Invite New Member
    </a>
</div>

<form method="GET" class="filter-bar">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, ID…" class="form-control search-input">
    <select name="role" class="form-control" onchange="this.form.submit()">
        <option value="">All Roles</option>
        @foreach($roles as $role)
        <option value="{{ $role->slug }}" {{ request('role') === $role->slug ? 'selected' : '' }}>{{ $role->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-outline">Search</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Clear</a>
</form>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Employee ID</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Courses</th>
                    <th>Last Login</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div class="avatar" style="width:34px;height:34px;font-size:.72rem;{{ $u->id === auth()->id() ? 'background:var(--teal);color:var(--white)' : '' }}">
                                {{ $u->initials() }}
                            </div>
                            <div>
                                <div style="font-weight:500;color:var(--gray-800)">
                                    {{ $u->name }}
                                    @if($u->id === auth()->id())
                                    <span class="badge badge-teal" style="margin-left:5px;font-size:.62rem">You</span>
                                    @endif
                                </div>
                                <div style="font-size:.75rem;color:var(--gray-400)">{{ $u->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:.84rem;color:var(--gray-500)">{{ $u->employee_id ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $u->role?->slug === 'admin' ? 'badge-danger' : 'badge-teal' }}">
                            {{ $u->role?->name ?? 'No role' }}
                        </span>
                    </td>
                    <td style="font-size:.84rem;color:var(--gray-500)">{{ $u->department ?? '—' }}</td>
                    <td>
                        <span style="font-size:.82rem;font-weight:600;color:var(--teal)">
                            {{ $u->courseAssignments->count() }}
                        </span>
                    </td>
                    <td style="font-size:.78rem;color:var(--gray-400)">
                        {{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Never' }}
                    </td>
                    <td>
                        <span class="badge {{ $u->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $u->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="actions">
                        <a href="{{ route('admin.users.show', $u) }}" class="btn btn-ghost btn-sm" title="View">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-ghost btn-sm" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        @if($u->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.toggle-status', $u) }}" style="display:inline">
                            @csrf
                            <button type="submit" class="btn btn-ghost btn-sm" title="{{ $u->is_active ? 'Deactivate' : 'Activate' }}">
                                @if($u->is_active)
                                <svg viewBox="0 0 24 24" fill="none" stroke="var(--warning)" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2" width="14" height="14"><polyline points="20,6 9,17 4,12"/></svg>
                                @endif
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
                            <h3>No staff members found</h3>
                            <p>Start by inviting your first team member.</p>
                            <a href="{{ route('admin.invitations.index') }}" class="btn btn-primary">Send Invitation</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="card-footer">
        <span style="font-size:.82rem;color:var(--gray-400)">{{ $users->total() }} members</span>
        <div class="pagination">
            @if(!$users->onFirstPage())<a href="{{ $users->previousPageUrl() }}" class="page-link">‹</a>@endif
            @foreach($users->getUrlRange(max(1,$users->currentPage()-2),min($users->lastPage(),$users->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="page-link {{ $page == $users->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach
            @if($users->hasMorePages())<a href="{{ $users->nextPageUrl() }}" class="page-link">›</a>@endif
        </div>
    </div>
    @endif
</div>
@endsection