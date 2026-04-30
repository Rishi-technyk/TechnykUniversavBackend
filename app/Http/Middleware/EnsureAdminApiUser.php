<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdminApiUser
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $role = strtolower((string) ($user->role ?? ''));

        if (!$user || (!($user->is_admin ?? false) && !str_contains($role, 'admin'))) {
            return response()->json([
                'status' => false,
                'message' => 'Admin access required.',
            ], 403);
        }

        return $next($request);
    }
}
