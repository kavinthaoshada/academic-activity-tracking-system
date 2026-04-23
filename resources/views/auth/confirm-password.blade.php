@extends('layouts.auth')
@section('title', 'Confirm Password')

@section('content')
<div class="auth-form-header">
    <h1>Confirm Password</h1>
    <p>This is a secure area of the application. Please confirm your password before continuing to enable Two-Factor Authentication.</p>
</div>

@if($errors->any())
<div class="alert alert-danger" style="margin-bottom: 22px;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span class="alert-message">{{ $errors->first() }}</span>
</div>
@endif

<form method="POST" action="{{ url('/user/confirm-password') }}">
    @csrf

    <div class="form-group">
        <label class="form-label" for="password">Password <span class="required">*</span></label>
        <div style="position:relative">
            <input id="password" type="password" name="password"
                   class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                   placeholder="••••••••" required autocomplete="current-password"
                   style="padding-right:42px" autofocus>
            <button type="button" onclick="togglePwd()" tabindex="-1"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--gray-400);padding:0">
                <svg id="eye-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
        </div>
        @error('password')<div class="invalid-feedback" style="display:block;margin-top:6px;">{{ $message }}</div>@enderror
    </div>

    <button type="submit" class="btn btn-teal btn-lg" style="width:100%;justify-content:center;margin-top:16px;">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Confirm Password
    </button>
    
    <div style="text-align:center;margin-top:20px;">
        <a href="{{ route('profile.show') }}" style="color:var(--gray-500);font-size:.84rem;text-decoration:none;">Cancel & Return to Profile</a>
    </div>
</form>

@push('scripts')
<script>
function togglePwd() {
    const inp = document.getElementById('password');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}
</script>
@endpush
@endsection