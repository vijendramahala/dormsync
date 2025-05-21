<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

class Authenticate extends Middleware
{
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'message' => 'Access Denied. Please login first.'
        ], 401);
    }

    protected function redirectTo($request): ?string
    {
        return null; // disable web-style redirect
    }
}
