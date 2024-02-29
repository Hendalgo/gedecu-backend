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
        $from = $request->query('from', now()->subYear());
        $to = $request->query('to', now());
        $currency = $request->query('currency');

        $validate = Validator::make($request->all(), [
            'period' => 'required|in:day,week,month,quarter,semester,year',
            'from' => 'date',
            'to' => 'date',
            'currency' => 'required|exists:currencies,id',
        ]);

        $validate->validate();

        $timezone = $request->header('TimeZone');
        $from = Carbon::parse($from, $timezone)->startOfDay();
        $to = Carbon::parse($to, $timezone)->endOfDay();
        $subreports = Subreport::with('report.type')
            ->where('currency_id', $currency)
            ->where(DB::raw('DATE(CONVERT_TZ(subreports.created_at, "+00:00", "'.$timezone.'"))'), '!=', null)
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->get()
            ->when(auth()->user()->role_id != 1, function ($query) {
                return $query->where('report.user_id', auth()->user()->id);
            })
            ->groupBy(['report.type.type', function ($subreport) use ($period) {
                return $this->getPeriod($subreport, $period);
            }])
            ->map(function ($subreports) {
                return $subreports->map(function ($subreport) {
                    return $subreport->sum('amount');
                });
            });

        return response()->json($subreports);
    }

    private function getPeriod($subreport, $period)
    {
        $date = $subreport->created_at;

        switch ($period) {
            case 'day':
                return $date->format('Y-m-d');
            case 'week':
                // Inicio y fin de la semana
                return $date->startOfWeek()->format('d-m-Y').' - '.$date->endOfWeek()->format('d-m-Y');
            case 'month':
                // Inicio y fin del mes
                return $date->startOfMonth()->format('d-m-Y').' - '.$date->endOfMonth()->format('d-m-Y');
            case 'quarter':
                // Inicio y fin del trimestre
                return $date->startOfQuarter()->format('d-m-Y').' - '.$date->endOfQuarter()->format('d-m-Y');
            case 'semester':
                // Inicio y fin del semestre
                $start = $date->month > 6 ? $date->firstOfJuly()->format('d-m-Y') : $date->firstOfJanuary()->format('d-m-Y');
                $end = $date->month > 6 ? $date->endOfYear()->format('d-m-Y') : $date->endOfJune()->format('d-m-Y');

                return $start.' - '.$end;
            case 'year':
                return $date->format('Y');
            default:
                return $date->startOfWeek()->format('d-m-Y').' - '.$date->endOfWeek()->format('d-m-Y');
        }
    }

    public function getTotalByCurrency()
    {
        $user = User::with('store')->find(auth()->user()->id);
        $banks_accounts = BankAccount::query();
        if ($user->role_id == 2) {
            $banks_accounts->where('user_id', $user->id);
        }
        if ($user->role_id == 3) {
            $store = $user->store->id;
            if ($store) {
                $banks_accounts->where('store_id', $store);
            } else {
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
            if ($lastHistory) {
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

    public function getTotalByBank()
    {
        $user = User::with('store')->find(auth()->user()->id);
        $banks_accounts = BankAccount::query();
        if ($user->role_id == 2) {
            $banks_accounts->where('user_id', $user->id);
        }
        if ($user->role_id == 3) {
            $store = $user->store->id;
            if ($store) {
                $banks_accounts->where('store_id', $store);
            } else {
                $banks_accounts->where('user_id', $user->id);
            }
        }
        $banks_accounts = $banks_accounts->where('banks_accounts.delete', false)
            ->where('account_type_id', '!=', 3)
            ->selectRaw('bank_id, SUM(balance) as total, currencies.id as currency_id, currencies.shortcode, currencies.symbol')
            ->leftJoin('currencies', 'banks_accounts.currency_id', '=', 'currencies.id')
            ->groupBy('bank_id', 'currency_id')
            ->with('bank')
            ->get();

        return response()->json($banks_accounts);
    }
}
