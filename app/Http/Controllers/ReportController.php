<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Movement;
use App\Models\Report;
use App\Models\User;
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
                $query = $query->whereNull('duplicated_status');
            }
        }
        if ($since) {
            $query = $query->whereDate('created_at', '>=', $since);
        }

        if ($until) {
            $query = $query->whereDate('created_at', '<=', $until);
        }
        if ($type) {
            $query = $query->where('type_id', $type);
        }
        
        if ($bank) {
            $query = $query->where('bank_id', $bank);

            if ($period === 'daily') {
                $subQuery = Movement::select('bank_id', DB::raw('DATE(created_at) as date'), DB::raw('MAX(id) as id'))
                    ->where('bank_id', $bank)
                    ->groupBy('bank_id', 'date');
            
                $query = Movement::from(DB::raw("({$subQuery->toSql()}) as sub"))
                    ->mergeBindings($subQuery->getQuery())
                    ->join('movements', 'movements.id', '=', 'sub.id')
                    ->select('movements.*')
                    ->orderBy('movements.created_at', 'desc');
            }
            else if ($period === 'week'){
                $subQuery = Movement::select('bank_id', DB::raw('WEEK(created_at) as week'), DB::raw('MAX(id) as id'))
                    ->where('bank_id', $bank)
                    ->groupBy('bank_id', 'week');
            
                $query = Movement::from(DB::raw("({$subQuery->toSql()}) as sub"))
                    ->mergeBindings($subQuery->getQuery())
                    ->join('movements', 'movements.id', '=', 'sub.id')
                    ->select('movements.*')
                    ->orderBy('movements.created_at', 'desc');
                
                return response()->json($query->paginate(10), 200);
            }
            elseif ($period === 'month'){
                $subQuery = Movement::select('bank_id', DB::raw('MONTH(created_at) as month'), DB::raw('MAX(id) as id'))
                    ->where('bank_id', $bank)
                    ->groupBy('bank_id', 'month');
            
                $query = Movement::from(DB::raw("({$subQuery->toSql()}) as sub"))
                    ->mergeBindings($subQuery->getQuery())
                    ->join('movements', 'movements.id', '=', 'sub.id')
                    ->select('movements.*')
                    ->orderBy('movements.created_at', 'desc');

            }
            elseif ($period === 'quarter'){
                $subQuery = Movement::select('bank_id', DB::raw('QUARTER(created_at) as quarter'), DB::raw('MAX(id) as id'))
                ->where('bank_id', $bank)
                ->groupBy('bank_id', 'quarter');
        
                $query = Movement::from(DB::raw("({$subQuery->toSql()}) as sub"))
                    ->mergeBindings($subQuery->getQuery())
                    ->join('movements', 'movements.id', '=', 'sub.id')
                    ->select('movements.*')
                    ->orderBy('movements.created_at', 'desc');
            }
            elseif ($period === 'semester'){ 
                $subQuery = Movement::select('bank_id', DB::raw('YEAR(created_at) as year'), DB::raw('CEILING(MONTH(created_at)/6) as semester'), DB::raw('MAX(id) as id'))
                ->where('bank_id', $bank)
                ->groupBy('bank_id', 'year', 'semester');
            
                $query = Movement::from(DB::raw("({$subQuery->toSql()}) as sub"))
                    ->mergeBindings($subQuery->getQuery())
                    ->join('movements', 'movements.id', '=', 'sub.id')
                    ->select('movements.*')
                    ->orderBy('movements.created_at', 'desc');
            }
            elseif ($period === 'year'){
                $subQuery = Movement::select('bank_id', DB::raw('YEAR(created_at) as year'), DB::raw('MAX(id) as id'))
                    ->where('bank_id', $bank)
                    ->groupBy('bank_id', 'year');
            
                $query = Movement::from(DB::raw("({$subQuery->toSql()}) as sub"))
                    ->mergeBindings($subQuery->getQuery())
                    ->join('movements', 'movements.id', '=', 'sub.id')
                    ->select('movements.*')
                    ->orderBy('movements.created_at', 'desc');
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
        
        $query = $query->with('bank', 'reports_types')->paginate(10);
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

                $bank->amount = $bank->amount + abs($request->amount);
                
                $bank->save(); 
                Movement::create([
                    'amount' => $request->amount,
                    'bank_amount' => $bank->amount,
                    'bank_id' => $bank->id,
                    'type' => 'income'
                ]);
            }
            else if ($request->type === 2 || $request->type === 7 || $request->type === 8){
                $bank = Bank::find($request->bank);

                $bank->amount = $bank->amount - abs($request->amount);
                
                $bank->save(); 
                Movement::create([
                    'amount' => $request->amount,
                    'bank_amount' => $bank->amount,
                    'bank_id' => $bank->id,
                    'type' => 'expense'
                ]);
            }
            else if ($request->type === 2){
                $bank = Bank::find($request->bank);

                $bank->amount = $bank->amount + abs($request->amount);
                
                $bank->save(); 
                Movement::create([
                    'amount' => $request->amount,
                    'bank_amount' => $bank->amount,
                    'bank_id' => $bank->id,
                    'type' => 'income'
                ]);
            }
            return response()->json(['message' => 'exito'], 201);
        }
        else{
            return response()->json(['error'=> 'Hubo un problema al crear el reporte'], 422);
        }
    }
    public function update (Request $request, $id){

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
}
