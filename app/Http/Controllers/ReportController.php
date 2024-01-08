<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Movement;
use App\Models\Report;
use App\Models\ReportType;
use App\Models\RoleReportPermission;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function index(Request $request){
        $currentUser = User::find(auth()->user()->id);

         // Get query parameters
        $order = $request->get('order', 'created_at');
        $orderBy = $request->get('order_by', 'desc');
        $duplicated = $request->get('duplicated');
        $duplicated_status = $request->get('duplicated_status');
        
        $date = $request->get('date');
        $since = $request->get('since');
        $until = $request->get('until');
        $inconsistence = $request->get('inconsistence_check');
        $bank = $request->get('bank');
        $period = $request->get('period');
        $search = $request->get('search');
        $movement = $request->get('movement');
        $type = $request->get('type_id');
        // Start query
        $query = Report::query();

        // Apply filters
        
        if ($search) {
            $query = $query->join('banks_accounts', 'reports.bank_account_id', "=", "banks_accounts.id")
               ->join('banks', 'banks_accounts.bank_id', "=", "banks.id")
               ->join('users', 'reports.user_id', "=", 'users.id')
               ->whereNotNull('reports.meta_data->store')
               ->join('stores', function ($join) {
                   $join->on('stores.id', '=', DB::raw("CAST(JSON_EXTRACT(reports.meta_data, '$.store') AS UNSIGNED)"));
               })
               ->select('reports.*', 'banks.name as bank_name');


            $query = $query->where(function ($query) use ($search) {
                $query->where('reports.amount', 'LIKE',  "%{$search}%")
                    ->orWhere('payment_reference', 'LIKE',  "%{$search}%")
                    ->orWhere('reports.meta_data', 'LIKE',  "%{$search}%")
                    ->orWhere('notes', 'LIKE',  "%{$search}%")
                    ->orWhere('banks.name', 'LIKE',  "%{$search}%")
                    ->orWhere('users.name', 'LIKE',  "%{$search}%")
                    ->orWhere('stores.name', 'LIKE',  "%{$search}%");
            });
        }
        if ($order) {
            $query = $query->orderBy($order, $orderBy);
        }
        if ($duplicated === 'yes') {
            if ($duplicated_status === 'done') {
                $query = $query->where('duplicated_status', 'done');
            }
            else if ($duplicated_status === 'cancel') {
                $query = $query->where('duplicated_status', 'cancel');
            }
            else{
                $query = $query->where('duplicated', true)->whereNull('duplicated_status');
            }
        }
        if ($since) {
            $query = $query->whereDate('reports.created_at', '>=', $since);
        }

        if ($until) {
            $query = $query->whereDate('reports.created_at', '<=', $until);
        }
        if ($type) {
            $query = $query->where('type_id', $type);
        }
        if ($date) {
            $query = $query->whereDate('reports.created_at', "=",$date);
        }
        if ($bank) {
            $query = $query->where('bank_id', $bank);

            if ($period === 'daily') {
                $days = 7;
                $now = Carbon::now();
                $results = [];
                $daysOfWeek = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
                if ($currentUser->role->id === 1) {
                    for ($i = 0; $i < $days; $i++) {
                        $date = $now->copy()->subDays($i);
                        $incomes = Movement::whereDate('created_at', $date)
                            ->where('type', 'income')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->sum('amount');
                        $expenses =  Movement::whereDate('created_at', $date)
                            ->where('type', 'expense')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->sum('amount');
                        $dayOfWeek = $daysOfWeek[$date->dayOfWeek]; // Get week day in spanish
                        $results[$dayOfWeek] = [
                            'incomes' => $incomes,
                            'expenses' => $expenses,
                        ];
                    }
                }
                else{
                    for ($i = 0; $i < $days; $i++) {
                        $date = $now->copy()->subDays($i);
                        $incomes = Movement::whereDate('created_at', $date)
                            ->where('type', 'income')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->whereHas('report', function ($query) use ($currentUser) {
                                $query->where('user_id', $currentUser->id);
                            })
                            ->sum('amount');
                        $expenses =  Movement::whereDate('created_at', $date)
                            ->where('type', 'expense')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->whereHas('report', function ($query) use ($currentUser) {
                                $query->where('user_id', $currentUser->id);
                            })
                            ->sum('amount');
                        $dayOfWeek = $daysOfWeek[$date->dayOfWeek]; // Get week day in spanish
                        $results[$dayOfWeek] = [
                            'incomes' => $incomes,
                            'expenses' => $expenses,
                        ];
                    }
                }
                return response()->json($results);

            }
            else if ($period === 'week'){
                $weeks = 8;
                $now = Carbon::now();
                $results = [];
                if ($currentUser->role->id === 1) {
                    for ($i = 0; $i < $weeks; $i++) {
                        $startOfWeek = $now->copy()->subWeeks($i)->startOfWeek();
                        $endOfWeek = $now->copy()->subWeeks($i)->endOfWeek();
                        $incomes = Movement::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                            ->where('type', 'income')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->sum('amount');
                        $expenses =  Movement::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                            ->where('type', 'expense')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->sum('amount');
                        $label = $startOfWeek->format('d, M') . ' - ' . $endOfWeek->format('d, M');
                        $results[$label] = [
                            'incomes' => $incomes,
                            'expenses' => $expenses,
                        ];
                    }
                }
                else{
                    for ($i = 0; $i < $weeks; $i++) {
                        $startOfWeek = $now->copy()->subWeeks($i)->startOfWeek();
                        $endOfWeek = $now->copy()->subWeeks($i)->endOfWeek();
                        $incomes = Movement::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                            ->where('type', 'income')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->whereHas('report', function ($query) use ($currentUser) {
                                $query->where('user_id', $currentUser->id);
                            })
                            ->sum('amount');
                        $expenses =  Movement::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                            ->where('type', 'expense')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->whereHas('report', function ($query) use ($currentUser) {
                                $query->where('user_id', $currentUser->id);
                            })
                            ->sum('amount');
                        $label = $startOfWeek->format('d, M') . ' - ' . $endOfWeek->format('d, M');
                        $results[$label] = [
                            'incomes' => $incomes,
                            'expenses' => $expenses,
                        ];
                    }
                }
                return response()->json($results);
            }
            elseif ($period === 'month'){
                $months = 12;
                $now = Carbon::now();
                $results = [];
                if ($currentUser->role->id === 1) {
                    
                    for ($i = 0; $i < $months; $i++) {
                        $date = $now->copy()->subMonths($i);
                        $startOfMonth = $now->copy()->subMonths($i)->startOfMonth();
                        $endOfMonth = $now->copy()->subMonths($i)->endOfMonth();
                        $incomes = Movement::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                            ->where('type', 'income')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->sum('amount');
                        $expenses =  Movement::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                            ->where('type', 'expense')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->sum('amount');
                        $label = $date->formatLocalized('%b');
                        $results[$label] = [
                            'incomes' => $incomes,
                            'expenses' => $expenses,
                        ];
                    }
                }
                else{
                    
                    for ($i = 0; $i < $months; $i++) {
                        $date = $now->copy()->subMonths($i);
                        $startOfMonth = $now->copy()->subMonths($i)->startOfMonth();
                        $endOfMonth = $now->copy()->subMonths($i)->endOfMonth();
                        $incomes = Movement::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                            ->where('type', 'income')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->whereHas('report', function ($query) use ($currentUser) {
                                $query->where('user_id', $currentUser->id);
                            })
                            ->sum('amount');
                        $expenses =  Movement::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                            ->where('type', 'expense')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->whereHas('report', function ($query) use ($currentUser) {
                                $query->where('user_id', $currentUser->id);
                            })
                            ->sum('amount');
                        $label = $date->formatLocalized('%b');
                        $results[$label] = [
                            'incomes' => $incomes,
                            'expenses' => $expenses,
                        ];
                    }
                }
                return response()->json($results);
            }     
            elseif ($period === 'quarter'){
                $trimesters = 6;
                $now = Carbon::now();
                $results = [];
            
                if ($currentUser->role->id === 1) {
                    
                    for ($i = 0; $i < $trimesters; $i++) {
                        $startOfTrimester = $now->copy()->subMonths(3*$i)->startOfMonth();
                        $endOfTrimester = $startOfTrimester->copy()->addMonths(3)->subDay();
                        $incomes = Movement::whereBetween('created_at', [$startOfTrimester, $endOfTrimester])
                            ->where('type', 'income')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->sum('amount');
                        $expenses =  Movement::whereBetween('created_at', [$startOfTrimester, $endOfTrimester])
                            ->where('type', 'expense')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->sum('amount');
                        // Get the trimester name in Spanish and the year
                        $label = $startOfTrimester->formatLocalized('%b') . ' - ' . $endOfTrimester->formatLocalized('%b, %Y');
                        $results[$label] = [
                            'start' => $startOfTrimester,
                            'end' => $endOfTrimester,
                            'incomes' => $incomes,
                            'expenses' => $expenses,
                        ];
                    }
                }
                else{
                    for ($i = 0; $i < $trimesters; $i++) {
                        $startOfTrimester = $now->copy()->subMonths(3*$i)->startOfMonth();
                        $endOfTrimester = $startOfTrimester->copy()->addMonths(3)->subDay();
                        $incomes = Movement::whereBetween('created_at', [$startOfTrimester, $endOfTrimester])
                            ->where('type', 'income')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->whereHas('report', function ($query) use ($currentUser) {
                                $query->where('user_id', $currentUser->id);
                            })
                            ->sum('amount');
                        $expenses =  Movement::whereBetween('created_at', [$startOfTrimester, $endOfTrimester])
                            ->where('type', 'expense')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->whereHas('report', function ($query) use ($currentUser) {
                                $query->where('user_id', $currentUser->id);
                            })
                            ->sum('amount');
                        // Get the trimester name in Spanish and the year
                        $label = $startOfTrimester->formatLocalized('%b') . ' - ' . $endOfTrimester->formatLocalized('%b, %Y');
                        $results[$label] = [
                            'incomes' => $incomes,
                            'expenses' => $expenses,
                        ];
                    }
                }
                return response()->json($results);
            }
            elseif ($period === 'semester'){
                $semesters = 4;
                $now = Carbon::now();
                $results = [];
            
                if ($currentUser->role->id === 1) {
                    
                    for ($i = 0; $i < $semesters; $i++) {
                        $startOfSemester = $now->copy()->subMonths(6*$i)->startOfMonth();
                        $endOfSemester = $startOfSemester->copy()->addMonths(6)->subDay();
                        $incomes = Movement::whereBetween('created_at', [$startOfSemester, $endOfSemester])
                            ->where('type', 'income')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->sum('amount');
                        $expenses =  Movement::whereBetween('created_at', [$startOfSemester, $endOfSemester])
                            ->where('type', 'expense')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->sum('amount');
                        // Get the semester name in Spanish and the year
                        $label = $startOfSemester->formatLocalized('%b') . ' - ' . $endOfSemester->formatLocalized('%b, %Y');
                        $results[$label] = [
                            'incomes' => $incomes,
                            'expenses' => $expenses,
                        ];
                    }
                }
                else{
                    for ($i = 0; $i < $semesters; $i++) {
                        $startOfSemester = $now->copy()->subMonths(6*$i)->startOfMonth();
                        $endOfSemester = $startOfSemester->copy()->addMonths(6)->subDay();
                        $incomes = Movement::whereBetween('created_at', [$startOfSemester, $endOfSemester])
                            ->where('type', 'income')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->whereHas('report', function ($query) use ($currentUser) {
                                $query->where('user_id', $currentUser->id);
                            })
                            ->sum('amount');
                        $expenses =  Movement::whereBetween('created_at', [$startOfSemester, $endOfSemester])
                            ->where('type', 'expense')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->whereHas('report', function ($query) use ($currentUser) {
                                $query->where('user_id', $currentUser->id);
                            })
                            ->sum('amount');
                        // Get the semester name in Spanish and the year
                        $label = $startOfSemester->formatLocalized('%b') . ' - ' . $endOfSemester->formatLocalized('%b, %Y');
                        $results[$label] = [
                            'incomes' => $incomes,
                            'expenses' => $expenses,
                        ];
                    }
                }
                return response()->json($results);
            }
            elseif ($period === 'year'){
                $years = 5;
                $now = Carbon::now();
                $results = [];
            
                if ($currentUser->role->id === 1) {
                    
                    for ($i = 0; $i < $years; $i++) {
                        $startOfYear = $now->copy()->subYears($i)->startOfYear();
                        $endOfYear = $now->copy()->subYears($i)->endOfYear();
                        $incomes = Movement::whereBetween('created_at', [$startOfYear, $endOfYear])
                            ->where('type', 'income')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->sum('amount');
                        $expenses =  Movement::whereBetween('created_at', [$startOfYear, $endOfYear])
                            ->where('type', 'expense')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->sum('amount');
                        // Get the year
                        $label = $startOfYear->formatLocalized('%Y');
                        $results[$label] = [
                            'incomes' => $incomes,
                            'expenses' => $expenses,
                        ];
                    }
                }
                else{
                    for ($i = 0; $i < $years; $i++) {
                        $startOfYear = $now->copy()->subYears($i)->startOfYear();
                        $endOfYear = $now->copy()->subYears($i)->endOfYear();
                        $incomes = Movement::whereBetween('created_at', [$startOfYear, $endOfYear])
                            ->where('type', 'income')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->whereHas('report', function ($query) use ($currentUser) {
                                $query->where('user_id', $currentUser->id);
                            })
                            ->sum('amount');
                        $expenses =  Movement::whereBetween('created_at', [$startOfYear, $endOfYear])
                            ->where('type', 'expense')
                            ->whereIn('bank_account_id', function ($query) use ($bank) {
                                $query->select('id')->from('banks_accounts')->where('bank_id', $bank);
                            })
                            ->whereHas('report', function ($query) use ($currentUser) {
                                $query->where('user_id', $currentUser->id);
                            })
                            ->sum('amount');
                        // Get the year
                        $label = $startOfYear->formatLocalized('%Y');
                        $results[$label] = [
                            'incomes' => $incomes,
                            'expenses' => $expenses,
                        ];
                    }
                }
                return response()->json($results);
            }
            
            $query = $query->where('type', '=', $movement);
            return response()->json($query->paginate(10), 200);
        }
        
        if ($currentUser->role->id === 1) {
            $query = $query->with('bank_account.bank.country', 'bank_account.bank.currency', 'type', 'user')->paginate(10);
            foreach ($query as $e) {
                if (isset(json_decode($e->meta_data)->store)) {
                    $e->store = Store::find(json_decode($e->meta_data)->store);
                }
            }
            foreach ($query as $e) {
                if (isset(json_decode($e->meta_data)->bank)) {
                    $e->bank = Bank::find(json_decode($e->meta_data)->bank);
                }
            }
            foreach ($query as $e) {
                if (isset(json_decode($e->meta_data)->account_manager)) {
                    $e->account_manager = User::find(json_decode($e->meta_data)->account_manager);
                }
            }
            
            return response()->json($query, 200);
        }
        $query = $query->where('reports.user_id', auth()->user()->id);
        
        $query = $query->with('bank_account.bank.country', 'bank_account.bank.currency', 'type', 'user')->paginate(10);
        foreach ($query as $e) {
            if (json_decode($e->meta_data)->store !== null) {
                $e->store = Store::find(json_decode($e->meta_data)->store);
            }
        }
        foreach ($query as $e) {
            if (isset(json_decode($e->meta_data)->bank)) {
                $e->bank = Bank::find(json_decode($e->meta_data)->bank);
            }
        }
        foreach ($query as $e) {
            if (isset(json_decode($e->meta_data)->account_manager)) {
                $e->account_manager = User::find(json_decode($e->meta_data)->account_manager);
            }
        }
        return response()->json($query, 200);
    }
    public function store(Request $request){
        $request->validate([
            'type_id' => 'required|exists:reports_types,id',
        ]);
        $role_report_permission = RoleReportPermission::where('role_id', auth()->user()->role->id)
            ->where('report_type_id', $request->type_id)
            ->first();
        if ($role_report_permission) {
            $request->validate([
                'subreports' => 'required|array',
            ]);
            $subreports = $request->subreports;
            $report_type = ReportType::find($request->type_id);
            $report_type_config = json_decode($report_type->meta_data, true);
            $validator = Validator::make([], []); // Crear una instancia de Validator vacía
            $validatedSubreports = [];
            foreach ($subreports as $subreport) {
                $reportValidations = $report_type_config['all'];
                foreach ($reportValidations as $validation) {
                    if (array_key_exists($validation['name'], $subreport)) {
                        $validator->setData([$validation['name'] => $subreport[$validation['name']]]);
                        $validator->setRules([$validation['name'] => $validation['validation']]);
                        if ($validator->fails()) {
                            $errorMessages = $validator->errors()->all();
                            return response()->json(['error' => 'Error de validación en el subreporte', 'validation_errors' => $errorMessages], 422);
                        }
                    } else {
                        return response()->json(['error' => 'Campo requerido no encontrado en el subreporte'], 422);
                    }
                }
                if (array_key_exists(auth()->user()->role->id, $report_type_config)) {
                    $reportValidationsEspecial = $report_type_config[auth()->user()->role->id];
                    foreach ($reportValidationsEspecial as $validation) {
                        if (array_key_exists($validation['name'], $subreport)) {
                            $validator->setData([$validation['name'] => $subreport[$validation['name']]]);
                            $validator->setRules([$validation['name'] => $validation['validation']]);
                            if ($validator->fails()) {
                                return response()->json(['error' => 'Error de validación en el subreporte'], 422);
                            }
                        } else {
                            return response()->json(['error' => 'Campo requerido no encontrado en el subreporte'], 422);
                        }
                    }
                }
                $validatedSubreports[] = $subreport;
            }
            $report = Report::create([
                'type_id' => $request->type_id,
                'user_id' => auth()->user()->id,
                'meta_data' => json_encode($validatedSubreports),
                'amount' => 0,
            ]);
            try {
                if ($report) {
                    $reportType = ReportType::find($request->type_id);
                    if ($reportType->type === 'income') {
                    if (!$reportType->country) {
                            foreach ($validatedSubreports as $subreport) {
                                if (array_key_exists('account_id', $subreport)) {
                                    $bankAccount = BankAccount::find($subreport['account_id']);
                                    if (array_key_exists('rate', $subreport)) {
                                        $bankAccount->balance = $bankAccount->balance + ($subreport['amount'] * $subreport['rate']);
                                        $bankAccount->save();
                                    }
                                    else{
                                        $bankAccount->balance = $bankAccount->balance + $subreport['amount'];
                                        $bankAccount->save();
                                    }
                                }
                                else if(array_key_exists("senderAccount_id", $subreport) && array_key_exists('receiverAccount_id', $subreport)){
                                    $senderBankAccount = BankAccount::find($subreport['senderAccount_id']);
                                    $receiverBankAccount = BankAccount::find($subreport['receiverAccount_id']);
                                    if (array_key_exists('rate', $subreport)) {
                                        $senderBankAccount->balance = $senderBankAccount->balance - ($subreport['amount'] * $subreport['rate']);
                                        $senderBankAccount->save();
                                        $receiverBankAccount->balance = $receiverBankAccount->balance + ($subreport['amount'] * $subreport['rate']);
                                        $receiverBankAccount->save();
                                    }
                                    else{
                                        $senderBankAccount->balance = $senderBankAccount->balance - $subreport['amount'];
                                        $senderBankAccount->save();
                                        $receiverBankAccount->balance = $receiverBankAccount->balance + $subreport['amount'];
                                        $receiverBankAccount->save();
                                    }
                                }
                                else{
                                    $report->delete();
                                    return response()->json(['error' => 'No se encontró una fuente a la cual ingresar los fondos'], 422);
                                }
                            }   
                    }
                    else{
                            foreach ($validatedSubreports as $subreport) {
                                if (array_key_exists('account_id', $subreport)) {
                                    $bankAccount = BankAccount::find($subreport['account_id']);
                                    if (array_key_exists('rate', $subreport)) {
                                        $bankAccount->balance = $bankAccount->balance + ($subreport['amount'] * $subreport['rate']);
                                        $bankAccount->save();
                                    }
                                    else{
                                        $bankAccount->balance = $bankAccount->balance + $subreport['amount'];
                                        $bankAccount->save();
                                    }
                                }
                                else if(array_key_exists('senderAccount_id', $subreport) && array_key_exists('receiverAccount_id', $subreport)){
                                    $senderBankAccount = BankAccount::find($subreport['senderAccount_id']);
                                    $receiverBankAccount = BankAccount::find($subreport['receiverAccount_id']);
                                    if (array_key_exists('rate', $subreport)) {
                                        $senderBankAccount->balance = $senderBankAccount->balance - ($subreport['amount'] * $subreport['rate']);
                                        $senderBankAccount->save();
                                        $receiverBankAccount->balance = $receiverBankAccount->balance + ($subreport['amount'] * $subreport['rate']);
                                        $receiverBankAccount->save();
                                    }
                                    else{
                                        $senderBankAccount->balance = $senderBankAccount->balance - $subreport['amount'];
                                        $senderBankAccount->save();
                                        $receiverBankAccount->balance = $receiverBankAccount->balance + $subreport['amount'];
                                        $receiverBankAccount->save();
                                    }
                                }
                                else if(array_key_exists('store_id', $subreport)){
                                    $bankAccount = BankAccount::where('store_id', $subreport['store_id'])->first();
                                    if (array_key_exists('rate', $subreport)) {
                                        $bankAccount->balance = $bankAccount->balance + ($subreport['amount'] * $subreport['rate']);
                                        $bankAccount->save();
                                    }
                                    else{
                                        $bankAccount->balance = $bankAccount->balance + $subreport['amount'];
                                        $bankAccount->save();
                                    }

                                }
                                else {
                                    $userStore = Store::where('user_id', auth()->user()->id)->first();

                                    $bankAccount = BankAccount::where('store_id', $userStore->id)->first();
                                    if(!$bankAccount){
                                        $report->delete();
                                        return response()->json(['error' => 'No se encontró una fuente a la cual ingresar los fondos'], 422);
                                    }
                                    if (array_key_exists('rate', $subreport)) {
                                        $bankAccount->balance = $bankAccount->balance + ($subreport['amount'] * $subreport['rate']);
                                        $bankAccount->save();
                                    }
                                    else{
                                        $bankAccount->balance = $bankAccount->balance + $subreport['amount'];
                                        $bankAccount->save();
                                    }
                                }
                            }
                    }
                    }
                    else if ($reportType->type === 'expense') {
                        if (!$reportType->country) {
                            foreach ($validatedSubreports as $subreport) {
                                if (array_key_exists('account_id', $subreport)) {
                                    $bankAccount = BankAccount::find($subreport['account_id']);
                                    if (array_key_exists('rate', $subreport)) {
                                        $bankAccount->balance = $bankAccount->balance - ($subreport['amount'] * $subreport['rate']);
                                        $bankAccount->save();
                                    }
                                    else{
                                        $bankAccount->balance = $bankAccount->balance - $subreport['amount'];
                                        $bankAccount->save();
                                    }
                                }
                                else if(array_key_exists("senderAccount_id", $subreport) && array_key_exists('receiverAccount_id', $subreport)){
                                    $senderBankAccount = BankAccount::find($subreport['senderAccount_id']);
                                    $receiverBankAccount = BankAccount::find($subreport['receiverAccount_id']);
                                    if (array_key_exists('rate', $subreport)) {
                                        $senderBankAccount->balance = $senderBankAccount->balance - ($subreport['amount'] * $subreport['rate']);
                                        $senderBankAccount->save();
                                        $receiverBankAccount->balance = $receiverBankAccount->balance + ($subreport['amount'] * $subreport['rate']);
                                        $receiverBankAccount->save();
                                    }
                                    else{
                                        $senderBankAccount->balance = $senderBankAccount->balance - $subreport['amount'];
                                        $senderBankAccount->save();
                                        $receiverBankAccount->balance = $receiverBankAccount->balance + $subreport['amount'];
                                        $receiverBankAccount->save();
                                    }
                                }
                                else{
                                    $report->delete();
                                    return response()->json(['error' => 'No se encontró una fuente a la cual restar los fondos'], 422);
                                }
                            }
                        }
                        else{
                            foreach ($validatedSubreports as $subreport) {
                                if (array_key_exists('account_id', $subreport)) {
                                    $bankAccount = BankAccount::find($subreport['account_id']);
                                    if (array_key_exists('rate', $subreport)) {
                                        $bankAccount->balance = $bankAccount->balance - ($subreport['amount'] * $subreport['rate']);
                                        $bankAccount->save();
                                    }
                                    else{
                                        $bankAccount->balance = $bankAccount->balance - $subreport['amount'];
                                        $bankAccount->save();
                                    }
                                }
                                else if(array_key_exists('store_id', $subreport)){
                                    $bankAccount = BankAccount::where('store_id', $subreport['store_id'])->first();
                                    if ($subreport['rate']) {
                                        $bankAccount->balance = $bankAccount->balance - ($subreport['amount'] * $subreport['rate']);
                                        $bankAccount->save();
                                    }
                                    else{
                                        $bankAccount->balance = $bankAccount->balance - $subreport['amount'];
                                        $bankAccount->save();
                                    }
                                }
                                else {
                                    $userStore = Store::where('user_id', auth()->user()->id)->first();

                                    $bankAccount = BankAccount::where('store_id', $userStore->id)->first();
                                    if(!$bankAccount){
                                        $report->delete();
                                        return response()->json(['error' => 'No se encontró una fuente a la cual restar los fondos'], 422);
                                    }
                                    if (array_key_exists('rate', $subreport)) {
                                        $bankAccount->balance = $bankAccount->balance - ($subreport['amount'] * $subreport['rate']);
                                        $bankAccount->save();
                                    }
                                    else{
                                        $bankAccount->balance = $bankAccount->balance - $subreport['amount'];
                                        $bankAccount->save();
                                    }
                                }
                            }
                        }
                    }
                    return response()->json($report, 201);
                } else {
                    $report->delete();
                    return response()->json(['error' => 'Hubo un problema al crear el reporte'], 500);
                }
            } catch (Error $th) {
                $report->delete();
                return response()->json(['error' => $th], 500);
            }
        } else {
            return response()->json(['error' => 'No tienes permiso para crear este tipo de reporte'], 401);
        }
    }
    public function update (Request $request, $id){

        $currentUser = User::find(auth()->user()->id);
        if ($currentUser->role->id === 1) {
            $validateRequest = $request->validate([
                'duplicated_status' => [
                    Rule::in(['done', 'cancel'])
                ],
                'inconsistence_check' =>'boolean'
            ]);
    
            $report = Report::find($id);
            if (isset($request->inconsistence_check)) {
                $report->inconsistence_check = $request->inconsistence_check;
    
                $report->save();
    
                return response()->json(['message' => 'Exito'], 201);
            }
            
            $report->duplicated_status = $request->duplicated_status;
    
            if ($report->save()) {
                
                $report = Report::with('type')->find($id);
                $bank = BankAccount::find($report->bank_account_id);
                if ($request->duplicated_status === 'done') {
                   if ($report->type->type === 'income') {
                        $bank->balance = $bank->balance - $report->amount;
                        $bank->save();
                   } 
                   elseif ($report->type->type === 'expense') {
                        $bank->balance = $bank->balance + $report->amount;
                        $bank->save();
                   }
                }
            }
            
            return response()->json(['message' => 'Exito'], 201);
        }
        return response()->json(['message' => 'forbiden'], 401);
    }
    public function destroy(Request $request, $id){
        $validateRequest = $request->validate([
            'id' => 'require|exist:reports,id'
        ]);

        $report = Report::find($id);
        if($report->type_id === 1){

        }
        else{
            
        }
    }
    public function getInconsistences(Request $request){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $order = $request->get('order', 'desc');
            $order_by = $request->get('order_by', 'created_at');
            $date = $request->get('date');
            $until = $request->get('until');
            $review = $request->get('reviewed');
            //Reports Expense to group in the same array report  Depositante (id 4) and Transferencia Enviada (id 2)
            $reportsE = Report::query()
                ->join('reports_types', 'reports.type_id', '=', 'reports_types.id')
                ->select('reports.*', 'reports_types.name as report_name', 'reports_types.type as operation_type');
            //Reports Income to group in the same array report Caja fuerte (id 3) and Peticion de transferencia (id 1)
            $reportsI = Report::query()
                ->join('reports_types', 'reports.type_id', '=', 'reports_types.id')
                ->select('reports.*', 'reports_types.name as report_name', 'reports_types.type as operation_type');
            
            $reportsE = $reportsE->orderBy($order_by, $order);
            $reportsI = $reportsI->orderBy($order_by, $order);
            if ($date) {
                $reportsE = $reportsE->whereDate('reports.created_at', $date);
                $reportsI = $reportsI->whereDate('reports.created_at', $date);
            }
            if ($review === 'yes') {
                $reportsE = $reportsE->where('reports.inconsistence_check', true);
                $reportsI = $reportsI->where('reports.inconsistence_check', true);
            }
            else{
                
                $reportsE = $reportsE->where('inconsistence_check', false);
                $reportsI = $reportsI->where('inconsistence_check', false);
            }
            $reportsE = $reportsE
                ->havingRaw('operation_type LIKE ?', ["expense"])
                ->with('bank_account.bank.country', 'bank_account.bank.currency', 'type', 'user')->paginate(2);
            foreach ($reportsE as $e) {
                if (isset(json_decode($e->meta_data)->store)) {
                    $e->store = Store::find(json_decode($e->meta_data)->store);
                }
            }
            foreach ($reportsE as $e) {
                if (isset(json_decode($e->meta_data)->bank)) {
                    $e->bank = Bank::find(json_decode($e->meta_data)->bank);
                }
            }
            foreach ($reportsE as $e) {
                if (isset(json_decode($e->meta_data)->account_manager)) {
                    $e->account_manager = User::find(json_decode($e->meta_data)->account_manager);
                }
            }
            $reportsI = $reportsI
                ->havingRaw('operation_type LIKE ?', ["income"])
                ->with('bank_account.bank.country', 'bank_account.bank.currency', 'type', 'user')->paginate(2);
            foreach ($reportsI as $e) {
                if (isset(json_decode($e->meta_data)->store)) {
                    $e->store = Store::find(json_decode($e->meta_data)->store);
                }
            }
            foreach ($reportsI as $e) {
                if (isset(json_decode($e->meta_data)->account_manager)) {
                    $e->account_manager = User::find(json_decode($e->meta_data)->account_manager);
                }
            }
            foreach ($reportsI as $e) {
                if (isset(json_decode($e->meta_data)->bank)) {
                    $e->bank = Bank::find(json_decode($e->meta_data)->bank);
                }
            }
            return response()->json([
                'income' => $reportsI,
                'expense' => $reportsE
            ], 200);
        }
        else{
            return response()->json(['error'=> 'Hubo un problema al crear el reporte'], 422);
        }
    }
}
