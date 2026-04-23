@extends('layouts.app')
@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div style="max-width:680px;display:flex;flex-direction:column;gap:16px">

    {{-- Profile info --}}
    <div class="card">
        <div class="card-header">
            <h3>Profile Information</h3>
        </div>
        <form method="POST" action="{{ route('user-profile-information.update') }}">
            @csrf @method('PUT')
            <div class="card-body">
                @if(session('status') === 'profile-information-updated')
                <div class="alert alert-success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="20,6 9,17 4,12"/></svg><span class="alert-message">Profile updated successfully.</span></div>
                @endif

                <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;padding:18px;background:var(--teal-pale);border-radius:var(--radius-md)">
                    <div class="avatar avatar-lg">{{ auth()->user()->initials() }}</div>
                    <div>
                        <div style="font-weight:600;color:var(--teal-dark)">{{ auth()->user()->name }}</div>
                        <div style="font-size:.82rem;color:var(--gray-400)">{{ auth()->user()->role?->name }}</div>
                        <div style="font-size:.78rem;color:var(--gray-400);margin-top:2px">Member since {{ auth()->user()->created_at->format('d M Y') }}</div>
                    </div>
                </div>

                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="required">*</span></label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                               class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address <span class="required">*</span></label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                               class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <span style="font-size:.78rem;color:var(--gray-400)">Changes take effect immediately.</span>
                <button type="submit" class="btn btn-teal">Save Profile</button>
            </div>
        </form>
    </div>

    {{-- Change password --}}
    <div class="card">
        <div class="card-header"><h3>Change Password</h3></div>
        <form method="POST" action="{{ route('user-password.update') }}">
            @csrf @method('PUT')
            <div class="card-body">
                @if(session('status') === 'password-updated')
                <div class="alert alert-success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="20,6 9,17 4,12"/></svg><span class="alert-message">Password updated successfully.</span></div>
                @endif

                <div class="form-group">
                    <label class="form-label">Current Password <span class="required">*</span></label>
                    <input type="password" name="current_password" class="form-control {{ $errors->has('current_password') ? 'is-invalid' : '' }}" autocomplete="current-password">
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label">New Password <span class="required">*</span></label>
                        <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" autocomplete="new-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password <span class="required">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <span></span>
                <button type="submit" class="btn btn-teal">Update Password</button>
            </div>
        </form>
    </div>

    {{-- Two-Factor Authentication --}}
    <div class="card">
        <div class="card-header">
            <h3>Two-Factor Authentication</h3>
            <span class="badge {{ auth()->user()->two_factor_confirmed_at ? 'badge-success' : 'badge-gray' }}">
                {{ auth()->user()->two_factor_confirmed_at ? 'Enabled' : 'Disabled' }}
            </span>
        </div>
        <div class="card-body">
            <p style="margin-bottom:16px;font-size:.875rem">
                Two-factor authentication adds an extra layer of security to your account by requiring a one-time code from your authenticator app when you sign in.
            </p>

            @if(auth()->user()->two_factor_confirmed_at)
                {{-- 2FA is enabled: show recovery codes + disable option --}}
                <div class="alert alert-success" style="margin-bottom:16px">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="20,6 9,17 4,12"/></svg>
                    <span class="alert-message">2FA is active. Your account is protected.</span>
                </div>

                <div style="margin-bottom:16px">
                    <button class="btn btn-outline btn-sm" onclick="toggleRecoveryCodes()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        View Recovery Codes
                    </button>
                </div>

                <div id="recoveryCodes" style="display:none;background:var(--gray-800);border-radius:var(--radius-md);padding:16px;margin-bottom:16px">
                    <div style="font-size:.72rem;font-weight:600;color:var(--gray-400);text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px">Recovery Codes — store these safely</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
                        @foreach(auth()->user()->recoveryCodes() as $code)
                        <code style="font-size:.82rem;color:var(--gold);background:rgba(251,186,0,0.08);padding:5px 10px;border-radius:4px;font-family:monospace">{{ $code }}</code>
                        @endforeach
                    </div>
                    {{-- Regenerate --}}
                    <form method="POST" action="{{ route('two-factor.recovery-codes') }}" style="margin-top:12px">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--gray-400)">Regenerate codes</button>
                    </form>
                </div>

                <form method="POST" action="{{ route('two-factor.disable') }}" onsubmit="return confirm('Disable 2FA? Your account will be less secure.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Disable Two-Factor Authentication</button>
                </form>

            @elseif(session('status') === 'two-factor-authentication-enabled')
                {{-- Just enabled — show QR code for confirmation --}}
                <div class="alert alert-info" style="margin-bottom:16px">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg>
                    <span class="alert-message">Scan the QR code with your authenticator app, then enter the 6-digit code to confirm.</span>
                </div>
                <div style="display:flex;gap:24px;align-items:flex-start;margin-bottom:20px">
                    <div style="background:white;padding:12px;border-radius:var(--radius-md);border:2px solid var(--gold);display:inline-block">
                        {!! auth()->user()->twoFactorQrCodeSvg() !!}
                    </div>
                    <div>
                        <div style="font-size:.84rem;font-weight:600;color:var(--gray-700);margin-bottom:8px">Setup Key</div>
                        <code style="font-size:.82rem;background:var(--gray-100);padding:8px 12px;border-radius:var(--radius-sm);display:block;word-break:break-all">
                            {{ decrypt(auth()->user()->two_factor_secret) }}
                        </code>
                    </div>
                </div>
                <form method="POST" action="{{ route('two-factor.confirm') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Confirmation Code <span class="required">*</span></label>
                        <input type="text" name="code" class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}"
                               inputmode="numeric" autocomplete="one-time-code" maxlength="6"
                               placeholder="000000" style="max-width:180px;font-size:1.2rem;letter-spacing:.2em;text-align:center">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Confirm &amp; Enable 2FA</button>
                </form>

            @else
                {{-- 2FA not enabled --}}
                <div style="display:flex;gap:12px;align-items:flex-start;padding:14px;background:var(--warning-bg);border-radius:var(--radius-md);margin-bottom:16px">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--warning)" stroke-width="2" style="flex-shrink:0;margin-top:1px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span style="font-size:.84rem;color:var(--warning)">2FA is not enabled. We strongly recommend enabling it for added security.</span>
                </div>
                <form method="POST" action="{{ route('two-factor.enable') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Enable Two-Factor Authentication
                    </button>
                </form>
            @endif
        </div>
    </div>

</div>

@push('scripts')
<script>
function toggleRecoveryCodes() {
    const el = document.getElementById('recoveryCodes');
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
@endpush
@endsection