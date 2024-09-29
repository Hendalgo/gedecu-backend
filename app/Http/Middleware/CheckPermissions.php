<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
{
    $user = auth()->user();
    if ($user->role_id === 1) {
        return $next($request);
    }

    $permissions = json_decode($user->permissions, true);
    $route = $request->route()->uri;
    $method = $request->method();

    if ($route === 'api/bank-accounts' && $method === 'POST') {
        // Verifica si 'allowed_currencies' es un array
        if (!isset($permissions['allowed_currencies']) || !is_array($permissions['allowed_currencies'])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Verifica si 'allowed_banks' es un array
        if (!isset($permissions['allowed_banks']) || !is_array($permissions['allowed_banks'])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!in_array($request->currency_id, $permissions['allowed_currencies'])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!in_array($request->bank_id, $permissions['allowed_banks'])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    return $next($request);
}
}
