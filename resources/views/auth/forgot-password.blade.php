@extends('layouts.auth')
@section('title', 'Reset Password')

@section('content')
<div class="auth-form-header">
    <h1>Reset Password</h1>
    <p>Enter your email address and we'll send you a link to reset your password.</p>
</div>

@if(session('status'))
<div class="alert alert-success">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="20,6 9,17 4,12"/></svg>
    <span class="alert-message">{{ session('status') }}</span>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/></svg>
    <span class="alert-message">{{ $errors->first() }}</span>
</div>
@endif

<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="form-group">
        <label class="form-label">Email address <span class="required">*</span></label>
        <input type="email" name="email" value="{{ old('email') }}"
               class="form-control" placeholder="you@skips.edu.in" required autofocus>
    </div>
    <button type="submit" class="btn btn-teal btn-lg" style="width:100%;justify-content:center">
        Send Reset Link
    </button>
</form>

<div style="text-align:center;margin-top:20px">
    <a href="{{ route('login') }}" style="font-size:.84rem;color:var(--gray-400)">← Back to Sign In</a>
</div>
@endsection