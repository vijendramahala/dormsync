<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserRole
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'You need to login to access this route.'], 401);
        }

        if (in_array($user->role, ['admin', 'superadmin'])) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized access.'], 403);
    }
}