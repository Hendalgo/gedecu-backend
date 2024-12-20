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

    return $next($request);
}
}
