<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            //Get the timezone from the header to set the timezone for the user
            $timezone = $request->header('TimeZone', 'America/Caracas');
            if(!$this->isValidTimeZone($timezone)){
                $timezone = 'America/Caracas';
            }
            $request->headers->set('TimeZone', $timezone);
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) throw new Exception();
            //Validate if the user is deleted
            if ($user->delete) {
                auth()->logout();
                throw new Exception();
            }
        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json([
                    "error" => "Token Invalido"
                ], 401);
            }
            if ($e instanceof TokenExpiredException) {
                return response()->json([
                    "error" => "Token Expirado"
                ], 401);
            }
            return response()->json([
                "error" => "Token no encontrado"
            ], 401);
        }
        
        return $next($request);
    }
    
    public function isValidTimeZone($timeZone) {
        return preg_match('/^[\+\-](0[0-9]|1[0-3]):[0-5][0-9]$/', $timeZone) === 1;
    }
}
