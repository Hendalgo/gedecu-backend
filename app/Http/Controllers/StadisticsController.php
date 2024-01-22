<?php

namespace App\Http\Controllers;

use App\Models\Subreport;
use App\Models\User;
use Illuminate\Http\Request;

class StadisticsController extends Controller
{
    public function getMovementsByPeriods(Request $request){
        $user = User::find(auth()->user()->id);

        $period = $request->get('period', 'month');
        $currency = $request->get('currency');

        $validations = [
            'period' => 'required|in:month,year,day,week,quarter,semester',
            'currency' => 'required|exists:currencies,id'
        ];

        $this->validate($request, $validations);

        $subreports = Subreport::query()
            ->leftJoin('reports', 'reports.id', '=', 'subreports.report_id');
        if($user->role_id != 1){
            $subreports->where('reports.user_id', $user->id);
        }
        $subreports = $subreports->where('reports.currency_id', $currency);

        switch ($period){
            case 'month':
                $subreports->where('subreports.created_at', '>=', now()->subMonth()->startOfMonth())
                    ->where('subreports.created_at', '<=', now()->subMonth()->endOfMonth());
                $subreports->selectRaw('MONTH(subreports.created_at) as period, SUM(subreports.amount) as total');
                break;
            case 'year':
                $subreports->selectRaw('YEAR(subreports.created_at) as period, SUM(subreports.amount) as total');
                break;
            case 'day':
                $subreports->selectRaw('DAY(subreports.created_at) as period, SUM(subreports.amount) as total');
                break;
            case 'week':
                $subreports->selectRaw('WEEK(subreports.created_at) as period, SUM(subreports.amount) as total');
                break;
            case 'quarter':
                $subreports->selectRaw('QUARTER(subreports.created_at) as period, SUM(subreports.amount) as total');
                break;
            case 'semester':
                $subreports->selectRaw('QUARTER(subreports.created_at) as period, SUM(subreports.amount) as total');
                break;
        }

        $subreports = $subreports->groupBy('period')->get();
    }
}
