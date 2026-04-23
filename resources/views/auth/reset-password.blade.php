@extends('layouts.auth')
@section('title', 'Set New Password')

@section('content')
<div class="auth-form-header">
    <h1>Set new password</h1>
    <p>Choose a strong password for your account.</p>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/></svg>
    <div class="alert-message">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
</div>
@endif

<form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $request->email) }}"
               class="form-control" readonly style="background:var(--gray-50)">
    </div>
    <div class="form-group">
        <label class="form-label">New Password <span class="required">*</span></label>
        <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required autocomplete="new-password">
    </div>
    <div class="form-group">
        <label class="form-label">Confirm Password <span class="required">*</span></label>
        <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat password" required autocomplete="new-password">
    </div>
    <button type="submit" class="btn btn-teal btn-lg" style="width:100%;justify-content:center">
        Reset Password
    </button>
</form>
@endsection