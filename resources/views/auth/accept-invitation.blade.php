@extends('layouts.auth')
@section('title', 'Accept Invitation')

@section('content')
<div class="auth-form-header">
    <div style="display:inline-flex;align-items:center;gap:8px;background:var(--gold-pale);border:1px solid var(--gold-light);color:var(--gold-dark);padding:6px 14px;border-radius:99px;font-size:.78rem;font-weight:600;margin-bottom:18px">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Invitation
    </div>
    <h1>Set up your account</h1>
    <p>Hello <strong>{{ $invitation->name }}</strong> — you've been invited by <strong>{{ $invitation->invitedBy->name }}</strong>. Create your password to get started.</p>
</div>

{{-- Invitation summary --}}
<div style="background:var(--teal-pale);border:1px solid var(--teal-light);border-radius:var(--radius-md);padding:14px 16px;margin-bottom:24px">
    <div class="info-row" style="padding:5px 0;border-bottom:1px solid var(--teal-light)">
        <span class="info-label" style="color:var(--teal)">Email</span>
        <span class="info-value">{{ $invitation->email }}</span>
    </div>
    <div class="info-row" style="padding:5px 0;border-bottom:1px solid var(--teal-light)">
        <span class="info-label" style="color:var(--teal)">Role</span>
        <span class="info-value">{{ $invitation->role->name }}</span>
    </div>
    @if($invitation->department)
    <div class="info-row" style="padding:5px 0;border-bottom:none">
        <span class="info-label" style="color:var(--teal)">Department</span>
        <span class="info-value">{{ $invitation->department }}</span>
    </div>
    @endif
</div>

@if($errors->any())
<div class="alert alert-danger">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div class="alert-message">
        @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
    </div>
</div>
@endif

<form method="POST" action="{{ route('invitation.store', $invitation->token) }}">
    @csrf

    <div class="form-group">
        <label class="form-label" for="password">Create Password <span class="required">*</span></label>
        <input id="password" type="password" name="password" class="form-control"
               placeholder="Minimum 8 characters" required autocomplete="new-password">
        <div class="form-hint">Use at least 8 characters with a mix of letters and numbers.</div>
    </div>

    <div class="form-group">
        <label class="form-label" for="password_confirmation">Confirm Password <span class="required">*</span></label>
        <input id="password_confirmation" type="password" name="password_confirmation"
               class="form-control" placeholder="Repeat your password" required autocomplete="new-password">
    </div>

    {{-- Password strength indicator --}}
    <div style="margin-bottom:22px">
        <div class="progress-wrap" style="margin-bottom:6px">
            <div class="progress-bar" id="strengthBar" style="width:0;background:var(--danger);transition:width 0.3s,background 0.3s"></div>
        </div>
        <div style="font-size:.72rem;color:var(--gray-400)" id="strengthLabel">Enter a password</div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Create Account & Sign In
    </button>
</form>

@push('scripts')
<script>
document.getElementById('password').addEventListener('input', function() {
    const v = this.value;
    const bar = document.getElementById('strengthBar');
    const lbl = document.getElementById('strengthLabel');
    let score = 0;
    if (v.length >= 8) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    const widths = ['0%', '25%', '50%', '75%', '100%'];
    const colors = ['var(--danger)', 'var(--danger)', 'var(--warning)', 'var(--gold)', 'var(--success)'];
    const labels = ['Too short', 'Weak', 'Fair', 'Good', 'Strong'];
    bar.style.width = widths[score];
    bar.style.background = colors[score];
    lbl.textContent = labels[score];
});
</script>
@endpush
@endsection