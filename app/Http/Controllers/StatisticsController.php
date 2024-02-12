<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Subreport;
use App\Models\TotalCurrenciesHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function getMovementsByPeriods(Request $request)
    {
        $period = $request->query('period', 'week');
        $from = $request->query('from', $this->getDefaultStartDate($period));
        $to = $request->query('to', now());

        $timezone = $request->header('timezone', 'UTC');
        $from = Carbon::parse($from, $timezone)->startOfDay();
        $to = Carbon::parse($to, $timezone)->endOfDay();

        $subreports = Subreport::with('report')
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->groupBy(['report.type', function ($subreport) use ($period, $timezone) {
                return $this->getPeriodLabel($subreport->created_at, $period, $timezone);
            }])
            ->map(function ($groupedSubreports) {
                return $groupedSubreports->sum('amount');
            });

        return response()->json($subreports);
    }

    private function getDateFormat($period)
    {
        switch ($period) {
            case 'day':
                return 'D d';
            case 'week':
                return 'W';
            case 'month':
                return 'M Y';
            case 'quarter':
                return '[Q]Q Y';
            case 'semester':
                return '[S]S Y';
            case 'year':
                return 'Y';
            default:
                return 'D d';
        }
    }

    private function getDefaultStartDate($period)
    {
        switch ($period) {
            case 'day':
                return now()->subDays(7);
            case 'week':
                return now()->subWeeks(12);
            case 'month':
                return now()->subMonths(6);
            case 'quarter':
                return now()->subQuarters(6);
            case 'semester':
                return now()->subMonths(6 * 6);
            case 'year':
                return now()->subYears(5);
            default:
                return now()->subWeek();
        }
    }
    private function getPeriodLabel($date, $period, $timezone)
    {
        $date = $date->setTimezone($timezone);
        switch ($period) {
            case 'day':
                return $date->format('D d');
            case 'week':
                $startOfWeek = $date->startOfWeek()->format('D d');
                $endOfWeek = $date->endOfWeek()->format('D d');
                return "$startOfWeek - $endOfWeek";
            case 'month':
                return $date->format('M Y');
            case 'quarter':
                return '[Q]Q Y';
            case 'semester':
                return '[S]S Y';
            case 'year':
                return 'Y';
            default:
                return $date->format('D d');
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
                $banks_accounts->where('store_id', $store)->where('account_type_id', "!=", 3);
            }
            else{
                $banks_accounts->where('user_id', $user->id);
            }
        }
        $banks_accounts = $banks_accounts->where('banks_accounts.delete', false)
            ->selectRaw('bank_id, SUM(balance) as total, currencies.id as currency_id, currencies.shortcode, currencies.symbol')
            ->leftJoin('currencies', 'banks_accounts.currency_id', '=', 'currencies.id')
            ->groupBy('bank_id', 'currency_id')
            ->with('bank')
            ->get();
        return response()->json($banks_accounts);
    }
}
