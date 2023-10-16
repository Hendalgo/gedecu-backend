<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Movement;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rule;
use Whoops\Run;

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
            $query = $query->join('banks', 'reports.bank_id', "=", "banks.id"  )
                ->select('reports.*', 'banks.name as bank_name');
            $query = $query->where('reports.amount', 'LIKE',  "%{$search}%")
                ->orWhere('payment_reference', 'LIKE',  "%{$search}%")
                ->orWhere('meta_data', 'LIKE',  "%{$search}%")
                ->orWhere('notes', 'LIKE',  "%{$search}%")
                ->orWhere('banks.name', 'LIKE',  "%{$search}%");
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
                for ($i = 0; $i < $days; $i++) {
                    $date = $now->copy()->subDays($i);
                    $incomes = Movement::whereDate('created_at', $date)
                        ->where('type', 'income')
                        ->where('bank_id', $bank)
                        ->sum('amount');
                    $expenses =  Movement::whereDate('created_at', $date)
                        ->where('type', 'expense')
                        ->where('bank_id', $bank)
                        ->sum('amount');
                    $dayOfWeek = $daysOfWeek[$date->dayOfWeek]; // Get week day in spanish
                    $results[$dayOfWeek] = [
                        'incomes' => $incomes,
                        'expenses' => $expenses,
                    ];
                }
                return response()->json($results);
            }
            else if ($period === 'week'){
                $weeks = 8;
                $now = Carbon::now();
                $results = [];

                for ($i = 0; $i < $weeks; $i++) {
                    $startOfWeek = $now->copy()->subWeeks($i)->startOfWeek();
                    $endOfWeek = $now->copy()->subWeeks($i)->endOfWeek();
                    $incomes = Movement::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                        ->where('type', 'income')
                        ->where('bank_id', $bank)
                        ->sum('amount');
                    $expenses =  Movement::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                        ->where('type', 'expense')
                        ->where('bank_id', $bank)
                        ->sum('amount');
                    $label = $startOfWeek->format('d, M') . ' - ' . $endOfWeek->format('d, M');
                    $results[$label] = [
                        'incomes' => $incomes,
                        'expenses' => $expenses,
                    ];
                }
                return response()->json($results);

            }
            elseif ($period === 'month'){
                $months = 12;
                $now = Carbon::now();
                $results = [];
                $monthNames = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                for ($i = 0; $i < $months; $i++) {
                    $date = $now->copy()->subMonths($i);
                    $startOfMonth = $now->copy()->subMonths($i)->startOfMonth();
                    $endOfMonth = $now->copy()->subMonths($i)->endOfMonth();
                    $incomes = Movement::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                        ->where('type', 'income')
                        ->where('bank_id', $bank)
                        ->sum('amount');
                    $expenses =  Movement::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                        ->where('type', 'expense')
                        ->where('bank_id', $bank)
                        ->sum('amount');
                    $label = $monthNames[$date->month - 1];
                    $results[$label] = [
                        'incomes' => $incomes,
                        'expenses' => $expenses,
                    ];
                }
                return response()->json($results);
            }
            elseif ($period === 'quarter'){
                $trimesters = 6;
                $now = Carbon::now();
                $results = [];

                $trimesterNames = ['Ene - Mar', 'Abr - Jun', 'Jul - Sep', 'Oct - Dic'];

                for ($i = 0; $i < $trimesters; $i++) {
                    $startOfTrimester = $now->copy()->subMonths(3*$i)->startOfMonth();
                    $endOfTrimester = $startOfTrimester->copy()->addMonths(3)->subDay();
                    $incomes = Movement::whereBetween('created_at', [$startOfTrimester, $endOfTrimester])
                        ->where('type', 'income')
                        ->where('bank_id', $bank)
                        ->sum('amount');
                    $expenses =  Movement::whereBetween('created_at', [$startOfTrimester, $endOfTrimester])
                        ->where('type', 'expense')
                        ->where('bank_id', $bank)
                        ->sum('amount');
                    // Get the trimester name in Spanish and the year
                    $label = $trimesterNames[$startOfTrimester->month > 9 ? 3 : ($startOfTrimester->month > 6 ? 2 : ($startOfTrimester->month > 3 ? 1 : 0))] . ', ' . $startOfTrimester->year;
                    $results[$label] = [
                        'incomes' => $incomes,
                        'expenses' => $expenses,
                    ];
                }
                return response()->json($results);
            }
            elseif ($period === 'semester'){ 
                $semesters = 4;
                $now = Carbon::now();
                $results = [];
                
                $semesterNames = ['Ene - Jun', 'Jul - Dic'];
                
                for ($i = 0; $i < $semesters; $i++) {
                    $startOfSemester = $now->copy()->subMonths(6*$i)->startOfMonth();
                    $endOfSemester = $startOfSemester->copy()->addMonths(6)->subDay();
                    $incomes = Movement::whereBetween('created_at', [$startOfSemester, $endOfSemester])
                        ->where('type', 'income')
                        ->where('bank_id', $bank)
                        ->sum('amount');
                    $egresos =  Movement::whereBetween('created_at', [$startOfSemester, $endOfSemester])
                        ->where('type', 'expense')
                        ->where('bank_id', $bank)
                        ->sum('amount');
                    $label = $semesterNames[$startOfSemester->month > 6 ? 1 : 0] . ', ' . $startOfSemester->year; // Obtiene el nombre del semestre en español y el año
                    $results[$label] = [
                        'incomes' => $incomes,
                        'egresos' => $egresos,
                    ];
                }
                return response()->json(array_reverse($results, true));
                
                
            }
            elseif ($period === 'year'){$years = 5;
                $now = Carbon::now();
                $results = [];
                
                for ($i = 0; $i < $years; $i++) {
                    $startOfYear = $now->copy()->subYears($i)->startOfYear();
                    $endOfYear = $now->copy()->subYears($i)->endOfYear();
                    $incomes = Movement::whereBetween('created_at', [$startOfYear, $endOfYear])
                        ->where('type', 'income')
                        ->where('bank_id', $bank)
                        ->sum('amount');
                    $expenses =  Movement::whereBetween('created_at', [$startOfYear, $endOfYear])
                        ->where('type', 'expense')
                        ->where('bank_id', $bank)
                        ->sum('amount');
                    // Get the year
                    $label = $startOfYear->year;
                    $results[$label] = [
                        'incomes' => $incomes,
                        'expenses' => $expenses,
                    ];
                }
                return response()->json($results);
                
            }
            $query = $query->where('type', '=', $movement);
            return response()->json($query->paginate(10), 200);
        }
        
        if ($currentUser->role->id === 1) {
            if ($inconsistence === 'check'){
                $query = $query->where('inconsistence_check', true);
            }
            $query = $query->with('bank.country.currency', 'type', 'store', 'user')->paginate(10);
            foreach ($query as $e) {
                if ($e->type_id === 1) {
                    $e->bank_income = Bank::with('country.currency')->find(json_decode($e->meta_data)->bank_income);
                }
            }
            return response()->json($query, 200);
        }
        $query = $query->where('user_id', auth()->user()->id);
        
        $query = $query->with('bank.country.currency', 'type', 'store', 'user')->paginate(10);
        foreach ($query as $e) {
            if ($e->type_id === 1) {
                $e->bank_income = Bank::with('country.currency')->find(json_decode($e->meta_data)->bank_income);
            }
        }
        return response()->json($query, 200);
    }
    public function store(Request $request){
        $currentUser = User::find(auth()->user()->id);
        $message = [
            'amount.numeric' => 'El monto debe ser un valor numérico.',
            'amount.required' => 'El campo monto es obligatorio.',
            'duplicated.required' => 'El campo duplicado es obligatorio.',
            'duplicated.boolean' => 'El campo duplicado es inválido',
            'rate.numeric' => 'La tasa debe ser un valor numérico.',
            'rate.required' => 'El campo tasa es obligatorio.',
            'bank_income.required' => 'El banco en el que ingresó el dinero es obligatorio.',
            'bank_income.exists' => 'El banco en el que ingresó el dinero es inválido.',
            'store.required' => 'El local es obligatorio.',
            'store.exists' => 'La local es inválido.',
            'received_from.string' => 'Recibido de debe ser una cadena de texto.',
            'payment_reference.string' => 'La referencia de pago debe ser una cadena de texto.',
            'notes.string' => 'Las notas deben ser una cadena de texto.',
            'type.required' => 'El campo tipo es obligatorio.',
            'type.exists' => 'El es inválido informes.',
            'bank.required' => 'El campo banco es obligatorio.',
            'bank.exists' => 'El banco es inválido'
        ];
        $validateRequest = $request->validate([
            'amount' => 'numeric|required',
            'duplicated' => 'required|boolean',
            'rate' => [
                'numeric',
                Rule::requiredIf(($request->type === 1 )|| ($request->type === 2 ))
            ],
            'bank_income' => [
                Rule::requiredIf($request->type === 1),
                'exists:banks,id'
            ], 
            'store' => 'required|exists:stores,id',
            'received_from' => 'string',
            'payment_reference' => 'string',
            'notes' => 'string',
            'type' => 'required|exists:reports_types,id',
            'bank' => 'required|exists:banks,id'
        ], $message);
        $validateRequest['inconsistence_check'] = false;
        $report = false;
        if ($request->type === 1) {
            $report =  Report::create([
                'amount'=> $request->amount,
                'duplicated' => $request->duplicated,
                'store_id' => $request->store,
                'notes' => $request->notes,
                'type_id' => $request->type,
                'bank_id' => $request->bank,
                'inconsistence_check' => false,
                'user_id' => $currentUser->id,
                'meta_data' => json_encode([
                    'rate'  => $request->rate,
                    'bank_income' => $request->bank_income
                ])
            ], $message);
        }
        else if($request->type === 2){
            $report =  Report::create([
                'amount'=> $request->amount,
                'duplicated' => $request->duplicated,
                'store_id' => $request->store,
                'notes' => $request->notes,
                'type_id' => $request->type,
                'bank_id' => $request->bank,
                'payment_reference' => $request->payment_reference,
                'inconsistence_check' => false,
                'user_id' => $currentUser->id,
                'meta_data' => json_encode([
                    'rate'  => $request->rate
                ])
            ], $message);
        }
        else{
            $report =  Report::create([
                'amount'=> $request->amount,
                'duplicated' => $request->duplicated,
                'store_id' => $request->store,
                'notes' => $request->notes,
                'type_id' => $request->type,
                'bank_id' => $request->bank,
                'inconsistence_check' => false,
                'user_id' => $currentUser->id,
                'meta_data' => json_encode([])
            ]);
        }
        
        if ($report) {
            if ($request->type === 1) {
                $bank = Bank::find($request->bank_income);

                Movement::create([
                    'amount' => $request->amount,
                    'bank_amount' => $bank->amount,
                    'bank_id' => $bank->id,
                    'report_id' => $report->id,
                    'type' => 'income'
                ]);
                $bank->amount = $bank->amount + abs($request->amount);
                
                $bank->save(); 
            }
            else if ($request->type === 2 || $request->type === 7 || $request->type === 8){
                $bank = Bank::find($request->bank);

                Movement::create([
                    'amount' => $request->amount,
                    'bank_amount' => $bank->amount,
                    'bank_id' => $bank->id,
                    'report_id' => $report->id,
                    'type' => 'expense'
                ]);
                $bank->amount = $bank->amount - abs($request->amount);
                
                $bank->save(); 
            }
            else if ($request->type === 6){
                $bank = Bank::find($request->bank);

                Movement::create([
                    'amount' => $request->amount,
                    'bank_amount' => $bank->amount,
                    'bank_id' => $bank->id,
                    'report_id' => $report->id,
                    'type' => 'income'
                ]);
                $bank->amount = $bank->amount + abs($request->amount);
                
                $bank->save(); 
            }
            return response()->json(['message' => 'exito'], 201);
        }
        else{
            return response()->json(['error'=> 'Hubo un problema al crear el reporte'], 422);
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
    
            $report->save();
            
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
            $reportsE = Report::query();
            //Reports Income to group in the same array report Caja fuerte (id 3) and Peticion de transferencia (id 1)
            $reportsI = Report::query();
            $reportsE = $reportsE->where(function ($query) {
                $query->where('type_id', '=', '4')
                      ->orWhere('type_id', '=', '2');
            });
            
            $reportsI = $reportsI->where(function ($query) {
                $query->where('type_id', '=', '3')
                      ->orWhere('type_id', '=', '1');
            });
            
            $reportsE = $reportsE->orderBy($order_by, $order);
            $reportsI = $reportsI->orderBy($order_by, $order);
            if ($date) {
                $reportsE = $reportsE->whereDate('created_at', $date);
                $reportsI = $reportsI->whereDate('created_at', $date);
            }
            if ($review === 'yes') {
                $reportsE = $reportsE->where('inconsistence_check', true);
                $reportsI = $reportsI->where('inconsistence_check', true);
            }
            else{
                
                $reportsE = $reportsE->where('inconsistence_check', false);
                $reportsI = $reportsI->where('inconsistence_check', false);
            }
            $reportsE = $reportsE->with('bank.country.currency', 'type', 'user', 'store')->paginate(2);
            $reportsI = $reportsI->with('bank.country.currency', 'type', 'user', 'store')->paginate(2);
            foreach ($reportsI as $e) {
                if ($e->type->id == 1) {
                    $e->bank_income = Bank::with('country.currency')->find(json_decode($e->meta_data)->bank_income);
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
