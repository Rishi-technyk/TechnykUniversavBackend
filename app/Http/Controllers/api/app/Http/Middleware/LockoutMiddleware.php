<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class LockoutMiddleware
{
    public function handle($request, Closure $next)
    {
        $ip = $request->ip();
        $attempts = Cache::get('login_attempts_' . $ip, 0);
        $lastAttempt = Cache::get('login_last_attempt_' . $ip);

        if ($attempts >= 3 && $lastAttempt && $lastAttempt->diffInMinutes(Carbon::now()) < 30) {
            // Lock the user out for 30 minutes
            return redirect()->route('login')->with('error', 'Too many login attempts. Please try again after 30 minutes.');
        }

        return $next($request);
    }
}
