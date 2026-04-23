@extends('layouts.auth')
@section('title', 'Invitation Expired')

@section('content')
<div style="text-align:center">
    <div style="width:72px;height:72px;border-radius:50%;background:var(--danger-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    </div>
    <h1 style="font-size:1.6rem;margin-bottom:10px">Invitation Expired</h1>
    <p style="margin-bottom:6px">This invitation for <strong>{{ $invitation->email }}</strong> expired on <strong>{{ $invitation->expires_at->format('d M Y') }}</strong>.</p>
    <p style="margin-bottom:28px;font-size:.875rem">Please contact your administrator to resend the invitation.</p>
    <a href="{{ route('login') }}" class="btn btn-teal">← Back to Sign In</a>
</div>
@endsection