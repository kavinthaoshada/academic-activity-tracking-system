@extends('layouts.auth')
@section('title', 'Two-Factor Challenge')
@section('content')
<div class="auth-form-header">
    <h1>Authentication Code</h1>
    <p>Enter the 6-digit code from your authenticator app.</p>
</div>
<form method="POST" action="{{ route('two-factor.login') }}">
    @csrf
    <div class="form-group">
        <label class="form-label">Code</label>
        <input type="text" name="code" class="form-control"
               inputmode="numeric" maxlength="6" placeholder="000000"
               style="font-size:1.4rem;letter-spacing:.3em;text-align:center" autofocus>
    </div>
    <button type="submit" class="btn btn-teal btn-lg" style="width:100%;justify-content:center">Verify</button>
</form>
@endsection