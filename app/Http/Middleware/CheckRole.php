<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $roles)
    {
        $user = $request->user();
        $allowed = explode('|', $roles);

        if (!$user || !in_array($user->role, $allowed)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
