<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userRole = auth()->user()->role;

        if (!in_array($userRole, $roles)) {
            return response()->json(['message' => 'Forbidden: Anda tidak memiliki akses'], 403);
        }

        return $next($request);
    }
}
