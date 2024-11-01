<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Report;
use App\Models\Store;
use App\Models\Subreport;
use App\Models\SubreportData;
use App\Models\TotalCurrenciesHistory;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserBalance;
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
            ->when($period === 'day', function ($query) {
                return $query->where('created_at', '>=', Carbon::now()->subDays(14));
            })
            ->when($period === 'week', function ($query) {
                return $query->where('created_at', '>=', Carbon::now()->subWeeks(14));
            })
            ->when($period === 'month', function ($query) {
                return $query->where('created_at', '>=', Carbon::now()->subMonths(12));
            })
            ->when($period === 'quarter', function ($query) {
                return $query->where('created_at', '>=', Carbon::now()->subQuarters(12));
            })
            ->when($period === 'year', function ($query) {
                return $query->where('created_at', '>=', Carbon::now()->subYears(5));
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
        $banks_accounts_type3 = BankAccount::query();

        if ($user->role_id == 2) {
            $banks_accounts->where('user_id', $user->id);
            $banks_accounts_type3->where('user_id', $user->id);
        }
        if ($user->role_id == 3) {
            //Check if the user has a store
            if ($user->store) {
                $store = $user->store->id;
                if ($store) {
                    $banks_accounts->where('store_id', $store);
                    $banks_accounts_type3->where('store_id', $store);
                } else {
                    $banks_accounts->where('user_id', $user->id);
                    $banks_accounts_type3->where('user_id', $user->id);
                }
            }
        }

        $banks_accounts = $banks_accounts->where('delete', false)->selectRaw('currency_id, SUM(balance) as total')
            ->where('account_type_id', '!=', 3)
            ->groupBy('currency_id')
            ->with('currency')
            ->get();

        $banks_accounts_type3 = $banks_accounts_type3->where('delete', false)->selectRaw('name, currency_id, SUM(balance) + (SELECT COALESCE(SUM(balance), 0) FROM user_balances WHERE user_balances.currency_id = banks_accounts.currency_id) as total')
            ->where('account_type_id', 3)
            ->groupBy('currency_id', 'name')
            ->with('currency')
            ->get();

        $banks_accounts = $banks_accounts->concat($banks_accounts_type3);
        if ($user->role_id == 1) {
            $bank_account_with_cash = BankAccount::where('currency_id', '!=', 2)
                ->where('delete', false)
                ->selectRaw('banks_accounts.currency_id, SUM(balance) + (SELECT COALESCE(SUM(balance), 0) FROM user_balances WHERE user_balances.currency_id = banks_accounts.currency_id) as total')
                ->groupBy('currency_id')
                ->with('currency')
                ->get();

            $bank_account_with_cash->map(function ($account) {
                $account->name = 'Cuenta + Efectivo';

                return $account;
            });

            $banks_accounts = $bank_account_with_cash->concat($banks_accounts);
        }

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
            //Check if the user has a store

            if ($user->store) {
                $store = $user->store->id;
                if ($store) {
                    $banks_accounts->where('store_id', $store);
                } else {
                    $banks_accounts->where('user_id', $user->id);
                }
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

    public function getTotalByBankBank(Request $request, $id)
    {
        if (auth()->user()->role_id != 1) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $banks_accounts = BankAccount::query();

        $user_id = $request->query('user_id');
        $user = null;
        if ($user_id) {
            $user = User::with('store')->find($user_id);
        }

        $banks_accounts = $banks_accounts->where('banks_accounts.delete', false)
            ->where('account_type_id', '!=', 3)
            ->where('bank_id', $id);

        if ($user != null) {
            if ($user->role_id == 2) {
                $banks_accounts->where('user_id', $user->id);
            }
            if ($user->role_id == 3) {
                //Check if the user has a store

                if ($user->store) {
                    $store = $user->store->id;
                    if ($store) {
                        $banks_accounts->where('store_id', $store);
                    } else {
                        $banks_accounts->where('user_id', $user->id);
                    }
                }
            }
        }
        $banks_accounts = $banks_accounts
            ->selectRaw('user_id, store_id, SUM(balance) as total, currencies.id as currency_id, currencies.shortcode, currencies.symbol')
            ->leftJoin('currencies', 'banks_accounts.currency_id', '=', 'currencies.id')
            ->groupBy('user_id', 'store_id', 'currency_id')
            ->with('user.role', 'store.user.role', 'currency')->get();

        return response()->json($banks_accounts);
    }

    public function getTotalByBankUser($id)
    {
        if (auth()->user()->role_id != 1) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $user = User::with('store')->find($id);
        $banks_accounts = BankAccount::query();
        if ($user->role_id == 2) {
            $banks_accounts->where('user_id', $id);
        }
        if ($user->role_id == 3) {
            //Check if the user has a store

            if ($user->store) {
                $store = $user->store->id;
                if ($store) {
                    $banks_accounts->where('store_id', $store);
                } else {
                    $banks_accounts->where('user_id', $id);
                }
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

    public function getTotalized(Request $request)
    {
        $currency = $request->get('currency');
        $transactionType = $request->get('type', "income");
        $timezone = $request->header('TimeZone', '-04:00');
        $date = $request->get('date', now($timezone));
        $reportType = $request->get('report');
    
        $transactions = Transaction::where('currency_id', $currency)
            ->where('type', $transactionType)
            ->where('created_at', '>=', Carbon::parse($date, $timezone)->startOfDay())
            ->where('created_at', '<=', Carbon::parse($date, $timezone)->endOfDay())
            ->when($reportType, function ($query) use ($reportType) {
                return $query->whereHas('subreport', function ($query) use ($reportType) {
                    $query->whereHas('report', function ($query) use ($reportType) {
                        $query->where('type_id', $reportType);
                    });
                });
            });
    
        $transactionsTotal = $transactions->sum('amount');
        $transactions = $transactions->get();
    
        $users = [];
    
        foreach ($transactions as $transaction) {
            $subreport = $transaction->subreport;
            $report = $subreport->report;
            $user = $report->user;
    
            if (!isset($users[$user->id])) {
                $users[$user->id] = [
                    'name' => $user->name,
                    'store' => $user->store ? ['name' => $user->store->name] : null,
                    'total_amount' => 0,
                    'bank_accounts' => [],
                ];
            }
    
            $users[$user->id]['total_amount'] += $transaction->amount;
    
            if ($transaction->account_id) {
                $bankAccount = $transaction->account;
                if (!isset($users[$user->id]['bank_accounts'][$bankAccount->id])) {
                    $users[$user->id]['bank_accounts'][$bankAccount->id] = [
                        'identifier' => $bankAccount->identifier,
                        'name' => $bankAccount->name,
                        'total_amount' => 0,
                    ];
                }
                $users[$user->id]['bank_accounts'][$bankAccount->id]['total_amount'] += $transaction->amount;
            } elseif ($transaction->balance_id) {
                $balance = $transaction->balance;
                if (!isset($users[$user->id]['bank_accounts'][$balance->id])) {
                    $users[$user->id]['bank_accounts'][$balance->id] = [
                        'identifier' => $balance->identifier,
                        'name' => $balance->name,
                        'total_amount' => 0,
                    ];
                }
                $users[$user->id]['bank_accounts'][$balance->id]['total_amount'] += $transaction->amount;
            }
        }
    
        $response = [
            'total' => $transactionsTotal,
            'users' => array_values($users),
        ];
    
        return response()->json($response, 200);
    }
    public function getFinalBalance(Request $request){
        if (auth()->user()->role_id != 1) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    
        $date = $request->get('date', now());
        $timezone = $request->header('TimeZone', '-04:00');

        // Obtener las cuentas de banco agrupadas por local
        $banks_accounts = BankAccount::query()
            ->where('banks_accounts.delete', false)
            ->where('status', 'active')
            ->where('account_type_id', '!=', 3)
            ->selectRaw('store_id, SUM(balance) as total, currencies.id as currency_id, currencies.shortcode, currencies.symbol')
            ->leftJoin('currencies', 'banks_accounts.currency_id', '=', 'currencies.id')
            ->groupBy('store_id', 'currency_id')
            ->with('store.user')
            ->get()
            ->filter(function ($account) {
                return !is_null($account->store_id);
            });

        // Obtener las cuentas de tipo 3 con delete en false y status en active
        $accounts_type_3 = BankAccount::query()
            ->where('banks_accounts.delete', false)
            ->where('account_type_id', 3)
            ->where('status', 'active')
            ->selectRaw('store_id, SUM(balance) as total3, currencies.id as currency_id, currencies.shortcode, currencies.symbol')
            ->leftJoin('currencies', 'banks_accounts.currency_id', '=', 'currencies.id')
            ->groupBy('store_id', 'currency_id')
            ->get()
            ->filter(function ($account) {
                return !is_null($account->store_id);
            });

        // Combinar los resultados
        $result = $banks_accounts->groupBy('store_id')->map(function ($accounts, $store_id) use ($accounts_type_3) {
            $store = $accounts->first()->store;
            $currencies = $accounts->map(function ($account) use ($accounts_type_3, $store_id) {
                $currency_id = $account->currency_id;
                $account_type_3 = $accounts_type_3->firstWhere('store_id', $store_id)->firstWhere('currency_id', $currency_id);
                return [
                    'currency_id' => $currency_id,
                    'shortcode' => $account->shortcode,
                    'symbol' => $account->symbol,
                    'total' => $account->total,
                    'total3' => $account_type_3 ? $account_type_3->balance : "No posee",
                ];
            });
            return [
                'store' => $store,
                'currencies' => $currencies
            ];
        })->values();

        return response()->json($result);
    }
    public function getFinalBalanceTransactions (Request $request){
        if (auth()->user()->role_id != 1) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    
        $date = $request->get('date', now());
    
        // Función para obtener y calcular los totales
        $calculateTotals = function ($reportId) {
            $subreports = Subreport::query()
                ->where('report_id', $reportId)
                ->with('currency', 'report.user.store')
                ->get();
    
            $rates = SubreportData::query()
                ->whereIn('subreport_id', $subreports->pluck('id'))
                ->where('key', 'rate')
                ->pluck('value', 'subreport_id');
    
            $totalOriginal = $subreports->sum('amount');
            $totalBolivares = $subreports->sum(function ($subreport) use ($rates) {
                $rate = $rates->get($subreport->id, 1);
                return $subreport->amount * $rate;
            });
    
            return [
                'total' => $totalOriginal,
                'total_bolivares' => $totalBolivares,
                'subreports' => $subreports
            ];
        };
    
        // Calcular los totales para los reportes de tipo 23, 4, 9 y 11
        $pesosGiros = $calculateTotals(23);
        $bolivaresGirosGestor = $calculateTotals(4);
        $bolivaresComisiones = $calculateTotals(9);
        $bolivaresOtros = $calculateTotals(11);
    
        // Calcular los totales para los subreportes de tipo expense y moneda id 2
        $subreportsExpenses = Subreport::query()
            ->whereHas('report', function ($query) {
                $query->whereHas('type', function ($query) {
                    $query->where('type', 'expense');
                });
            })
            ->whereHas('currency', function ($query) {
                $query->where('id', 2);
            })
            ->when($date, function ($query) use ($date) {
                return $query->whereDate('created_at', Carbon::parse($date));
            })
            ->with('report.user.store', 'currency')
            ->get();
    
        $totalOriginalExpenses = $subreportsExpenses->sum('amount');
    
        $bolivaresDelDia = [
            'total' => $totalOriginalExpenses,
            'subreports' => $subreportsExpenses
        ];
    
        return response()->json([
            'pesos_giros' => $pesosGiros,
            'bolivares_giros_gestor' => $bolivaresGirosGestor,
            'bolivares_comisiones' => $bolivaresComisiones,
            'bolivares_otros' => $bolivaresOtros,
            'bolivares_del_dia' => $bolivaresDelDia
        ]);
    }
}
