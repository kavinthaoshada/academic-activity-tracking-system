@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('breadcrumb')
    <a href="{{ route('admin.users.index') }}">Staff</a>
    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    <span>Edit</span>
@endsection

@section('content')
<div style="max-width:700px">
<form method="POST" action="{{ route('admin.users.update', $user) }}">
    @csrf @method('PUT')

    <div class="card" style="margin-bottom:16px">
        <div class="card-header">
            <div style="display:flex;align-items:center;gap:12px">
                <div class="avatar avatar-lg">{{ $user->initials() }}</div>
                <div>
                    <h3 style="font-family:var(--font-display);font-size:1.1rem">{{ $user->name }}</h3>
                    <div style="font-size:.78rem;color:var(--gray-400)">{{ $user->email }}</div>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg>
                <div class="alert-message">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
            </div>
            @endif

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span class="required">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" required>
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Employee ID</label>
                    <input type="text" name="employee_id" value="{{ old('employee_id', $user->employee_id) }}" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control">
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" value="{{ old('department', $user->department) }}" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Role <span class="required">*</span></label>
                    <select name="role_id" class="form-control" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                        @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                        @endforeach
                    </select>
                    @if($user->id === auth()->id())
                    <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                    <div class="form-hint">You cannot change your own role.</div>
                    @endif
                </div>
            </div>

            <div class="section-divider"><span>Security</span></div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                    <div class="form-hint">Only fill this to reset the user's password.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat new password">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:0">
                <label class="toggle-wrap">
                    <div class="toggle">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                        <span class="toggle-slider"></span>
                    </div>
                    <span style="font-size:.875rem;font-weight:500;color:var(--gray-700)">Account is Active</span>
                </label>
                @if($user->id === auth()->id())
                <input type="hidden" name="is_active" value="1">
                @endif
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </div>
</form>
</div>
@endsection