<?php

namespace App\Http\Middleware;

use App\Http\Controllers\RoleController;
use App\Models\User;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permissions): Response
    {
        try{
            $user = User::find(auth()->user()->id);

        }catch(Exception $e){

        }
        return $next($request);
    }
}
