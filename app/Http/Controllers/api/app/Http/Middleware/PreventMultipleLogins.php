<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class PreventMultipleLogins
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->session_token !== $request->session()->get('session_token')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login')->with('error', 'You have been logged out.');
        }

        return $next($request);
    }
}
