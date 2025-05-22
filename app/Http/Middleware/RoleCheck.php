<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($role === 'student' && !$request->user()->isStudent()) {
            return response()->json(['message' => 'Access denied. Student role required.'], 403);
        }

        if ($role === 'instructor' && !$request->user()->isInstructor()) {
            return response()->json(['message' => 'Access denied. Instructor role required.'], 403);
        }

        return $next($request);
    }
}
