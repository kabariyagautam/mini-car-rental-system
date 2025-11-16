<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$roles)
    {
        // User not logged in → return 401
        if (! $request->user()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // User logged in but doesn't have required role → 403
        if (! in_array($request->user()->role, $roles)) {
            return response()->json([
                'status' => false,
                'message' => 'You do not have permission to access this resource'
            ], 403);
        }

        return $next($request);
    }

}
