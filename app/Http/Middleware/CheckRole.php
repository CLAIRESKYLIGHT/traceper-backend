<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $roles)
    {
         $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $allowed = is_string($roles) ? explode('|', $roles) : (array)$roles;

        if (! in_array($user->role, $allowed)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
