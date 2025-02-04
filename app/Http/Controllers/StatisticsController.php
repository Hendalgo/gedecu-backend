<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Currency;
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
    public function getNewTotalized(Request $request)
    {
        $timezone = $request->header('TimeZone', '-04:00');
        $from = null;
        $to = null;
        $reportType = $request->get('report');

        // Determinar la moneda y el tipo de reporte basado en el tipo pasado por el usuario
        $currency = 1; // Moneda por defecto
        $reportTypeId = null;
        $reportTypeField = null;

        switch ($reportType) {
            case 0:
                $currency = 1;
                $reportTypeId = 23;
                break;
            case 1:
                $currency = 1;
                $reportTypeId = 4;
                break;
            case 2:
                $currency = 2;
                $reportTypeId = 1;
                break;
            case 3:
                $currency = 2;
                $reportTypeField = 'income';
                break;
            case 4:
                $currency = 1;
                $reportTypeId = 13;
                break;
            case 6:
                $currency = 2;
                $reportTypeField = 'expense';
                break;
            case 7:
                $currency = 1;
                $reportTypeId = 17;
                break;
            case 8:
                $currency = 1;
                $reportTypeId = 37;
                break;
            case 9:
                $currency = 1;
                $reportTypeId = 19;
                break;
            case 10:
                $currency = 1;
                $reportTypeId = 25;
                break;
            case 11:
                $currency = 1;
                $reportTypeId = 21;
                break;
        }

        $period = $request->get('period', 'day');

        if ($period == 'day') {
            $date = now($timezone);
            $from = Carbon::parse($date, $timezone)->startOfDay();
            $to = Carbon::parse($date, $timezone)->endOfDay();
        } else if ($period == 'yesterday') {
            $date = now($timezone)->subDay();
            $from = Carbon::parse($date, $timezone)->startOfDay();
            $to = Carbon::parse($date, $timezone)->endOfDay();
        } else if ($period == 'month') {
            $date = now($timezone);
            $from = Carbon::parse($date, $timezone)->startOfMonth();
            $to = Carbon::parse($date, $timezone)->endOfMonth();
        } else {
            $date = now($timezone)->subMonth();
            $from = Carbon::parse($date, $timezone)->startOfMonth();
            $to = Carbon::parse($date, $timezone)->endOfMonth();
        }

        $transactions = Transaction::where('currency_id', $currency)
            ->when($reportTypeField, function ($query) use ($reportTypeField) {
                return $query->where('type', $reportTypeField);
            })
            ->whereHas('subreport', function ($query) use ($from, $to, $reportTypeId, $reportTypeField) {
                $query->whereBetween('created_at', [$from, $to])
                    ->when($reportTypeId, function ($query) use ($reportTypeId) {
                        return $query->whereHas('report', function ($query) use ($reportTypeId) {
                            $query->where('type_id', $reportTypeId);
                        });
                    });
            });

        $transactionsTotal = $transactions->sum('amount');
        $transactions = $transactions->get();
        $users = [];

        // Obtener el shortcode de la moneda
        $currencyShortcode = Currency::find($currency)->shortcode;

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
                $bank = $bankAccount->bank;
                if (!isset($users[$user->id]['bank_accounts'][$bankAccount->id])) {
                    $users[$user->id]['bank_accounts'][$bankAccount->id] = [
                        'identifier' => $bankAccount->identifier,
                        'name' => $bankAccount->name,
                        'bank' => $bank ? ['name' => $bank->name] : null,
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

        // Concatenar los montos con el shortcode de la moneda al principio y formatear los montos
        foreach ($users as &$user) {
            $user['total_amount'] = $currencyShortcode . ' ' . number_format($user['total_amount'], 2, ',', '.');
            foreach ($user['bank_accounts'] as &$account) {
                $account['total_amount'] = $currencyShortcode . ' ' . number_format($account['total_amount'], 2, ',', '.');
            }
        }

        $response = [
            'total' => $currencyShortcode . ' ' . number_format($transactionsTotal, 2, ',', '.'),
            'users' => array_values($users),
        ];

        return response()->json($response, 200);
    }
    public function getTotalized(Request $request)
    {
        $currency = $request->get('currency');
        $transactionType = $request->get('type', "income");
        $timezone = $request->header('TimeZone', '-04:00');
        $from = null;
        $to = null;
        $reportType = $request->get('report');

        $period = $request->get('period', 'day');

        if ($period == 'day') {
            $date = now($timezone);
            $from = Carbon::parse($date, $timezone)->startOfDay();
            $to = Carbon::parse($date, $timezone)->endOfDay();
        } else if ($period == 'yesterday') {
            $date = now($timezone)->subDay();
            $from = Carbon::parse($date, $timezone)->startOfDay();
            $to = Carbon::parse($date, $timezone)->endOfDay();
        } else if ($period == 'month') {
            $date = now($timezone);
            $from = Carbon::parse($date, $timezone)->startOfMonth();
            $to = Carbon::parse($date, $timezone)->endOfMonth();
        } else {
            $date = now($timezone)->subMonth();
            $from = Carbon::parse($date, $timezone)->startOfMonth();
            $to = Carbon::parse($date, $timezone)->endOfMonth();
        }
    
        $transactions = Transaction::where('currency_id', $currency)
            ->where('type', $transactionType)
            ->whereHas('subreport', function ($query) use ($from, $to) {
                $query->whereBetween('created_at', [$from, $to]);
            })
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
                $bank = $bankAccount->bank;
                if (!isset($users[$user->id]['bank_accounts'][$bankAccount->id])) {
                    $users[$user->id]['bank_accounts'][$bankAccount->id] = [
                        'identifier' => $bankAccount->identifier,
                        'name' => $bankAccount->name,
                        'bank' => $bank ? ['name' => $bank->name] : null,
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

        $role = $request->get('role', 3);
        $result = [];
        if($role == 3){
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
                ->where('status', 'active')->get()->toArray();
            // Combinar los resultados
            $result = $banks_accounts->groupBy('store_id')->map(function ($accounts, $store_id) use ($accounts_type_3) {
                $store = $accounts->first()->store;
                $currencies = $accounts->map(function ($account) use ($accounts_type_3, $store_id) {
                    $currency_id = $account->currency_id;
                $account_type_3 = collect($accounts_type_3)->firstWhere('store_id', $store_id);
                
                    return [
                        'currency_id' => $currency_id,
                        'shortcode' => $account->shortcode,
                        'symbol' => $account->symbol,
                        'total' => $account->total,
                        'total3' => $account_type_3['balance'],
                    ];
                });
                return [
                    'store' => $store,
                    'currencies' => $currencies
                ];
            })->values();
        }else{
            // Obtener las cuentas de banco agrupadas por usuario
            $banks_accounts = BankAccount::query()
                ->where('banks_accounts.delete', false)
                ->where('status', 'active')
                ->where('account_type_id', '!=', 3)
                ->selectRaw('user_id, SUM(balance) as total, currencies.id as currency_id, currencies.shortcode, currencies.symbol')
                ->leftJoin('currencies', 'banks_accounts.currency_id', '=', 'currencies.id')
                ->leftJoin('users', 'banks_accounts.user_id', '=', 'users.id')
                ->where('users.role_id', $role)
                ->groupBy('user_id', 'currency_id')
                ->with('user')
                ->get()
                ->filter(function ($account) {
                    return !is_null($account->user_id);
                });

            // Combinar los resultados
            $result = $banks_accounts->groupBy('user_id')->map(function ($accounts, $user_id) {
                $user = $accounts->first()->user;
                $currencies = $accounts->map(function ($account) {
                    return [
                        'currency_id' => $account->currency_id,
                        'shortcode' => $account->shortcode,
                        'symbol' => $account->symbol,
                        'total' => $account->total,
                    ];
                });
                return [
                    'user' => $user,
                    'currencies' => $currencies
                ];
            })->values();
        }
        return response()->json($result);
    }
    public function getFinalBalanceTransactions (Request $request){
        if (auth()->user()->role_id != 1) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $period =  $request->get('period', 'day');
        $timezone = $request->header('TimeZone', '-04:00');
        $from = null;
        $to = null;
    
        if($period == 'day'){
            $date = now();
            $from = Carbon::parse($date, $timezone)->startOfDay();
            $to = Carbon::parse($date, $timezone)->endOfDay();
        }else if($period == 'yesterday'){
            $date = now()->subDay();
            $from = Carbon::parse($date, $timezone)->startOfDay();
            $to = Carbon::parse($date, $timezone)->endOfDay();
        }else if($period == 'month'){
            $date = now();
            $from = Carbon::parse($date, $timezone)->startOfMonth();
            $to = Carbon::parse($date, $timezone)->endOfMonth();
        }else{
            $date = now()->subMonth();
            $from = Carbon::parse($date, $timezone)->startOfMonth();
            $to = Carbon::parse($date, $timezone)->endOfMonth();
        }
    
        // Función para obtener y calcular los totales
        $calculateTotals = function ($reportId, $includeBolivares = false) use ($date, $from, $to) {
            $subreports = Subreport::query()
                ->whereHas('report', function ($query) use ($reportId) {
                    $query->where('type_id', $reportId);
                })
                ->with('currency', 'report.user.store')
                ->when($date, function ($query) use ($date, $from, $to) {
                    return $query->whereBetween('created_at', [$from, $to]);
                })
                ->get()
                ->groupBy('report.user_id');
    
            if ($subreports->isEmpty()) {
                return $includeBolivares ? [
                    'total' => 0,
                    'total_bolivares' => 0,
                    'subreports' => []
                ] : [
                    'total' => 0,
                    'subreports' => []
                ];
            }
    
            $totals = $subreports->map(function ($userSubreports, $userId) use ($includeBolivares) {
                $totalOriginal = $userSubreports->sum('amount');
                $result = [
                    'user_id' => $userId,
                    'total' => $totalOriginal,
                    'subreports' => $userSubreports
                ];
    
                if ($includeBolivares) {
                    $rates = SubreportData::query()
                        ->whereIn('subreport_id', $userSubreports->pluck('id'))
                        ->where('key', 'rate')
                        ->pluck('value', 'subreport_id');
    
                    $totalBolivares = $userSubreports->sum(function ($subreport) use ($rates) {
                        $rate = $rates->get($subreport->id, 1);
                        return $subreport->amount / $rate;
                    });
    
                    $result['total_bolivares'] = $totalBolivares;
                }
    
                return $result;
            });
    
            return $includeBolivares ? [
                'total' => $totals->sum('total'),
                'total_bolivares' => $totals->sum('total_bolivares'),
                'subreports' => $totals->values()
            ] : [
                'total' => $totals->sum('total'),
                'subreports' => $totals->values()
            ];
        };
    
        // Calcular los totales para los reportes de tipo 23, 4, 9 y 11
        $pesosGiros = $calculateTotals(23, true);
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
            ->when($date, function ($query) use ($date, $from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->with('report.user.store', 'currency')
            ->get()
            ->groupBy('report.user_id');
    
        if ($subreportsExpenses->isEmpty()) {
            $bolivaresDelDia = [
                'total' => 0,
                'subreports' => []
            ];
        } else {
            $totals = $subreportsExpenses->map(function ($userSubreports, $userId) {
                $totalOriginal = $userSubreports->sum('amount');
                return [
                    'user_id' => $userId,
                    'total' => $totalOriginal,
                    'subreports' => $userSubreports
                ];
            });
    
            $bolivaresDelDia = [
                'total' => $totals->sum('total'),
                'subreports' => $totals->values()
            ];
        }
    
        return response()->json([
            'pesos_giros' => $pesosGiros,
            'bolivares_giros_gestor' => $bolivaresGirosGestor,
            'bolivares_comisiones' => $bolivaresComisiones,
            'bolivares_otros' => $bolivaresOtros,
            'bolivares_del_dia' => $bolivaresDelDia
        ]);
    }
}
