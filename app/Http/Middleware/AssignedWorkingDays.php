<?php

namespace App\Http\Middleware;

use App\Services\FilterWorkingDay;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
            $assignedWorkingDays = new FilterWorkingDay($user, $request->header('TimeZone', 'America/Caracas'));

            $workingDays = $assignedWorkingDays->filterWorkingDay();

            if ($workingDays->isEmpty() && $user->role->id === 2) {
                return response()->json([
                    'error' => 'No se encontraron días laborales asignados',
                ], 401);
            }

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error procesando la petición',
            ], 500);
        }

        return $next($request);
    }
}
