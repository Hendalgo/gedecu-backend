<?php

namespace App\Http\Middleware;

use App\Services\FilterWorkingDay;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function PHPUnit\Framework\isEmpty;

class AssignedWorkingDays
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = auth()->user();
            $assignedWorkingDays = new FilterWorkingDay($user, $request->header('timezone', '-04:00'));
            $workingDays = $assignedWorkingDays->filterWorkingDay();
            
            if (empty($workingDays) && $user->role->id === 2) {
                return response()->json([
                    'error' => 'No se encontraron días laborales asignados',
                ], 401);
            }

        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }

        return $next($request);
    }
}
