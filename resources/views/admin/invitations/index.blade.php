@extends('layouts.app')
@section('title', 'Invitations')
@section('page-title', 'Staff Invitations')
@section('breadcrumb')
    <span>Invitations</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-text">
        <h1>Staff Invitations</h1>
        <p>Invite faculty members to join the system</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('inviteModal')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Invite Staff Member
    </button>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Name &amp; Email</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Invited By</th>
                    <th>Sent</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invitations as $inv)
                <tr>
                    <td>
                        <div style="font-weight:500;color:var(--gray-800)">{{ $inv->name }}</div>
                        <div style="font-size:.78rem;color:var(--gray-400)">{{ $inv->email }}</div>
                    </td>
                    <td><span class="badge {{ $inv->role->slug === 'admin' ? 'badge-danger' : 'badge-teal' }}">{{ $inv->role->name }}</span></td>
                    <td style="font-size:.84rem;color:var(--gray-500)">{{ $inv->department ?? '—' }}</td>
                    <td style="font-size:.82rem;color:var(--gray-500)">{{ $inv->invitedBy->name }}</td>
                    <td style="font-size:.8rem;color:var(--gray-400)">{{ $inv->created_at->format('d M Y') }}</td>
                    <td style="font-size:.8rem;color:{{ $inv->isExpired() ? 'var(--danger)' : 'var(--gray-400)' }}">
                        {{ $inv->expires_at->format('d M Y') }}
                    </td>
                    <td>
                        @if($inv->isAccepted())
                            <span class="badge badge-success">Accepted</span>
                        @elseif($inv->isExpired())
                            <span class="badge badge-danger">Expired</span>
                        @else
                            <span class="badge badge-warning">Pending</span>
                        @endif
                    </td>
                    <td class="actions">
                        @if($inv->isPending() && !$inv->isExpired())
                        <form method="POST" action="{{ route('admin.invitations.resend', $inv) }}" style="display:inline">
                            @csrf
                            <button type="submit" class="btn btn-ghost btn-sm" title="Resend">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="1,4 1,10 7,10"/><path d="M3.51 15a9 9 0 1 0 .49-3.95"/></svg>
                            </button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('admin.invitations.destroy', $inv) }}" style="display:inline"
                              onsubmit="return confirm('Delete this invitation?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-ghost btn-sm" title="Delete">
                                <svg viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" width="14" height="14"><polyline points="3,6 5,6 21,6"/><path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6m3,0V4a1,1,0,0,1,1-1h4a1,1,0,0,1,1,1v2"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </div>
                            <h3>No invitations sent yet</h3>
                            <p>Invite your first staff member to get started.</p>
                            <button class="btn btn-primary" onclick="openModal('inviteModal')">Send First Invitation</button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invitations->hasPages())
    <div class="card-footer">
        <span style="font-size:.82rem;color:var(--gray-400)">{{ $invitations->total() }} total</span>
        <div class="pagination">
            @if(!$invitations->onFirstPage())<a href="{{ $invitations->previousPageUrl() }}" class="page-link">‹</a>@endif
            @foreach($invitations->getUrlRange(1, $invitations->lastPage()) as $page => $url)
                <a href="{{ $url }}" class="page-link {{ $page == $invitations->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach
            @if($invitations->hasMorePages())<a href="{{ $invitations->nextPageUrl() }}" class="page-link">›</a>@endif
        </div>
    </div>
    @endif
</div>

{{-- Invite Modal --}}
<div class="modal-backdrop {{ $errors->any() ? 'open' : '' }}" id="inviteModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Invite Staff Member</h3>
            <button class="btn btn-ghost btn-sm" onclick="closeModal('inviteModal')" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.invitations.store') }}">
            @csrf
            <div class="modal-body">
                @if($errors->any())
                <div class="alert alert-danger" style="margin-bottom:16px">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg>
                    <div class="alert-message">
                        @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
                    </div>
                </div>
                @endif
                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="required">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="Dr. First Last" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address <span class="required">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="staff@skips.edu.in" required>
                    </div>
                </div>
                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label">Employee ID</label>
                        <input type="text" name="employee_id" value="{{ old('employee_id') }}" class="form-control" placeholder="EMP-001">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="+91 98765 43210">
                    </div>
                </div>
                <div class="form-row form-row-2">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" value="{{ old('department') }}" class="form-control" placeholder="Computer Science">
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-control">
                            <option value="staff" {{ old('role', 'staff') === 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('inviteModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22,2 15,22 11,13 2,9 22,2"/></svg>
                    Send Invitation
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); document.body.style.overflow = 'hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }
document.querySelectorAll('.modal-backdrop').forEach(b => b.addEventListener('click', function(e) { if(e.target === this) closeModal(this.id); }));
</script>
@endpush
@endsection