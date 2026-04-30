<?php

namespace App\Http\Middleware;

use Closure;

class RestrictHttpMethods
{
    public function handle($request, Closure $next)
    {
        if (!in_array($request->method(), ['GET', 'POST', 'HEAD'])) {
            return response()->json(['message' => 'Method Not Allowed'], 405);
        }

        return $next($request);
    }
}
