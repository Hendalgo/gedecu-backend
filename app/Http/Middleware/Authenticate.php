<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request &$request): ?string
    {   
        //Validate TimeZone is Valid with format "-04:30"
        $timezone = $request->header('TimeZone');
        if(!$this->isValidTimeZone($timezone)){
            $timezone = 'America/Caracas';
        }
        return $request->expectsJson() ? null : route('login');
    }
    public function isValidTimeZone($timeZone) {
        return preg_match('/^[\+\-](0[0-9]|1[0-3]):[0-5][0-9]$/', $timeZone) === 1;
    }
}
