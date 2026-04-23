@extends('layouts.auth')
@section('title', 'Sign In')

@section('content')
<div class="auth-form-header">
    <h1>Welcome back</h1>
    <p>Sign in to your account to continue</p>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span class="alert-message">{{ $errors->first() }}</span>
</div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="form-group">
        <label class="form-label" for="email">Email address <span class="required">*</span></label>
        <input id="email" type="email" name="email" value="{{ old('email') }}"
               class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
               placeholder="you@skips.edu.in" required autofocus autocomplete="email">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="password">
            Password <span class="required">*</span>
            <a href="{{ route('password.request') }}"
               style="font-weight:400;float:right;color:var(--teal-mid);font-size:.78rem">
                Forgot password?
            </a>
        </label>
        <div style="position:relative">
            <input id="password" type="password" name="password"
                   class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                   placeholder="••••••••" required autocomplete="current-password"
                   style="padding-right:42px">
            <button type="button" onclick="togglePwd()"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--gray-400);padding:0">
                <svg id="eye-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
        </div>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
        <label class="toggle-wrap" style="cursor:pointer">
            <div class="toggle">
                <input type="checkbox" name="remember" id="remember">
                <span class="toggle-slider"></span>
            </div>
            <span style="font-size:.84rem;color:var(--gray-600)">Remember me</span>
        </label>
    </div>

    <button type="submit" class="btn btn-teal btn-lg" style="width:100%;justify-content:center">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        Sign In
    </button>
</form>

{{-- 2FA form (hidden — shown by Fortify when 2FA is enabled) --}}
@if(session('login.id'))
<div class="auth-divider"></div>
<p style="font-size:.84rem;color:var(--gray-500);text-align:center;margin-bottom:16px">
    Two-factor authentication is enabled on your account.
</p>
<form method="POST" action="{{ route('two-factor.login') }}">
    @csrf
    <div class="form-group">
        <label class="form-label">Authentication Code</label>
        <input type="text" name="code" class="form-control" placeholder="6-digit code"
               inputmode="numeric" autocomplete="one-time-code" maxlength="6">
    </div>
    <button type="submit" class="btn btn-teal" style="width:100%;justify-content:center">Verify</button>
</form>
@endif

@push('scripts')
<script>
function togglePwd() {
    const inp = document.getElementById('password');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}
</script>
@endpush
@endsection