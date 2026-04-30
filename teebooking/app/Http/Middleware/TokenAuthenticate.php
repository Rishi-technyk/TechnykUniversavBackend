<?php

namespace App\Http\Middleware;

use App\Models\AC_UserMaster;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class TokenAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken() ?? $request->input('access_token');

       
        if (!$token) {
            return response()->json(['status' => false, 'message' => 'Access token required'], 401);
        }

        $user = AC_UserMaster::where('access_token', $token)->first();
      

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Invalid access token'], 401);
        }

        $request->setUserResolver(fn() => $user);

   

    return $next($request);
    }
}
