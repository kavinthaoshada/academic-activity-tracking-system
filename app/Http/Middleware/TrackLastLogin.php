<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrackLastLogin
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && is_null($request->user()->last_login_at)) {
            $request->user()->update(['last_login_at' => now()]);
        }

        return $next($request);
    }
}