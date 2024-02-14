<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Subreport;
use App\Models\TotalCurrenciesHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StatisticsController extends Controller
{
    public function getMovementsByPeriods(Request $request)
    {
        $period = $request->query('period', 'week');
        $from = $request->query('from');
        $to = $request->query('to');
        $currency = $request->query('currency');

        Validator::make($request->all(), [
            'period' => 'required|in:day,week,month,quarter,semester,year',
            'from' => 'date',
            'to' => 'date',
            'currency' => 'required|exists:currencies,id'
        ]);

        $timezone = $request->header('timezone', 'UTC');
        $from = Carbon::parse($from, $timezone)->startOfDay();
        $to = Carbon::parse($to, $timezone)->endOfDay();

        $subreports = Subreport::with('report.type')
            ->where('currency_id', $currency)
            ->where(DB::raw('DATE(CONVERT_TZ(reports.created_at, "+00:00", "'.$timezone.'"))'))
            ->when( $from && $to, function($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->get()
            ->groupBy(['report.type.type', function ($subreport) use ($period) {
                return $subreport->groupBy('created_at', function ($subreport) use ($period) {
                    return $this->getPeriod($period, $subreport);
                });
            }]);
        return response()->json($subreports);
    }

    private function getPeriod($period, $subreport)
    {
        switch ($period) {
            case 'day':
                return $subreport->groupBy('DATE(created_at)');
            case 'week':
                return $subreport->groupBy('WEEK(created_at)');
            case 'month':
                return $subreport->groupBy('MONTH(created_at)');
            case 'quarter':
                return $subreport->groupBy('QUARTER(created_at)');
            case 'semester':
                return $subreport->groupBy('MONTH(created_at) > 6');
            case 'year':
                return $subreport->groupBy('YEAR(created_at)');
            default:
                return $subreport->groupBy('DATE(created_at)');
        }
    }

    public function getTotalByCurrency(){
        $user = User::with('store')->find(auth()->user()->id);
        $banks_accounts = BankAccount::query();
        if($user->role_id == 2){
            $banks_accounts->where('user_id', $user->id);
        }
        if($user->role_id == 3){
            $store = $user->store->id;
            if($store){
                $banks_accounts->where('store_id', $store);
            }
            else{
                $banks_accounts->where('user_id', $user->id);
            }
        }
        $banks_accounts = $banks_accounts->where('delete', false)->selectRaw('currency_id, SUM(balance) as total')
            ->groupBy('currency_id')
            ->with('currency')
            ->get();
    
        $banks_accounts = $banks_accounts->map(function ($account) {
            $accountArray = $account->toArray();
    
            $lastHistory = TotalCurrenciesHistory::where('currency_id', $account->currency_id)
                ->latest('created_at')
                ->first();
            if($lastHistory) {
                $previousTotal = $lastHistory->total;
                $currentTotal = $accountArray['total'];
                $change = $currentTotal - $previousTotal;
                $percentageChange = 0;
                if ($previousTotal != 0) {
                    $percentageChange = ($change / abs($previousTotal)) * 100;
                }
                $accountArray['percentage'] = $percentageChange;
            }
            return $accountArray;
        });
    
        return response()->json($banks_accounts);
    }
    public function getTotalByBank(){
        $user = User::with('store')->find(auth()->user()->id);
        $banks_accounts = BankAccount::query();
        if($user->role_id == 2){
            $banks_accounts->where('user_id', $user->id);
        }
        if($user->role_id == 3){
            $store = $user->store->id;
            if($store){
                $banks_accounts->where('store_id', $store);
            }
            else{
                $banks_accounts->where('user_id', $user->id);
            }
        }
        $banks_accounts = $banks_accounts->where('banks_accounts.delete', false)
            ->where('account_type_id', "!=", 3)
            ->selectRaw('bank_id, SUM(balance) as total, currencies.id as currency_id, currencies.shortcode, currencies.symbol')
            ->leftJoin('currencies', 'banks_accounts.currency_id', '=', 'currencies.id')
            ->groupBy('bank_id', 'currency_id')
            ->with('bank')
            ->get();
        return response()->json($banks_accounts);
    }
}
